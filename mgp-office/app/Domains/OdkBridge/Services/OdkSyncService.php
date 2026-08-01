<?php

declare(strict_types=1);

namespace App\Domains\OdkBridge\Services;

use App\Domains\Form\Models\Form;
use App\Settings\OdkSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Orchestre la synchronisation ODK Central → Laravel.
 *
 * Flux :
 *   1. Pour chaque formulaire PACIPA actif (lu depuis la table `forms`),
 *      récupère les soumissions nouvelles depuis le dernier curseur de sync.
 *   2. Télécharge le XML de chaque soumission non traitée.
 *   3. Délègue à SubmissionMapper (avec le form_type MGP) pour créer/mettre à jour les modèles.
 *   4. Marque la soumission comme traitée dans ODK Central.
 *   5. Met à jour le curseur de sync.
 */
class OdkSyncService
{
    /** Type MGP VBG — géré via syncVbg() avec logs et contrôles séparés. */
    private const VBG_TYPE = 'F03';

    /** Préfixe des curseurs Redis pour la pagination des soumissions. */
    private const CURSOR_PREFIX = 'odk_sync_cursor:';

    /** TTL curseur : 30 jours. */
    private const CURSOR_TTL = 60 * 60 * 24 * 30;

    public function __construct(
        private readonly OdkApiClient     $apiClient,
        private readonly SubmissionMapper $mapper,
        private readonly OdkSettings      $settings,
    ) {}

    // ---------------------------------------------------------------
    //  Points d'entrée
    // ---------------------------------------------------------------

    /**
     * Synchronise tous les formulaires non-VBG actifs (form_type ≠ F03).
     *
     * @return array{processed: int, skipped: int, errors: int}
     */
    public function sync(): array
    {
        if (! $this->settings->sync_enabled) {
            Log::info('OdkSyncService — sync désactivée (OdkSettings::sync_enabled = false)');
            return ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $forms = Form::where('is_active', true)
            ->whereNotNull('form_type')
            ->where('form_type', '!=', self::VBG_TYPE)
            ->get();

        if ($forms->isEmpty()) {
            Log::info('OdkSyncService — aucun formulaire actif non-VBG dans la table forms.');
            return ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $totals = ['processed' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($forms as $form) {
            $result = $this->syncForm($form->form_id, $form->form_type);
            $totals['processed'] += $result['processed'];
            $totals['skipped']   += $result['skipped'];
            $totals['errors']    += $result['errors'];
        }

        Log::info('OdkSyncService — sync terminée', $totals);

        return $totals;
    }

    /**
     * Synchronise uniquement le(s) formulaire(s) VBG (F03).
     *
     * @return array{processed: int, skipped: int, errors: int}
     */
    public function syncVbg(): array
    {
        if (! $this->settings->sync_enabled) {
            return ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $forms = Form::where('is_active', true)
            ->where('form_type', self::VBG_TYPE)
            ->get();

        if ($forms->isEmpty()) {
            Log::channel('vbg')->info('OdkSyncService VBG — aucun formulaire F03 actif trouvé.');
            return ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $totals = ['processed' => 0, 'skipped' => 0, 'errors' => 0];

        foreach ($forms as $form) {
            $result = $this->syncForm($form->form_id, $form->form_type, logChannel: 'vbg');
            $totals['processed'] += $result['processed'];
            $totals['skipped']   += $result['skipped'];
            $totals['errors']    += $result['errors'];
        }

        Log::channel('vbg')->info('OdkSyncService VBG — sync terminée', $totals);

        return $totals;
    }

    /**
     * Synchronise un formulaire spécifique par son xmlFormId ODK.
     * Utilisé par le webhook pour une sync ciblée immédiate.
     *
     * @return array{processed: int, skipped: int, errors: int}
     */
    public function syncByFormId(string $xmlFormId): array
    {
        $form = Form::where('form_id', $xmlFormId)->where('is_active', true)->first();

        if (! $form || ! $form->form_type) {
            Log::warning("OdkSyncService::syncByFormId — formulaire inconnu ou sans form_type : {$xmlFormId}");
            return ['processed' => 0, 'skipped' => 0, 'errors' => 0];
        }

        $logChannel = $form->form_type === self::VBG_TYPE ? 'vbg' : 'stack';

        return $this->syncForm($form->form_id, $form->form_type, logChannel: $logChannel);
    }

    // ---------------------------------------------------------------
    //  Sync d'un formulaire
    // ---------------------------------------------------------------

    private function syncForm(string $xmlFormId, string $formType, string $logChannel = 'stack'): array
    {
        $since     = $this->getCursor($xmlFormId);
        $processed = 0;
        $skipped   = 0;
        $errors    = 0;

        try {
            $submissions = $this->apiClient->listSubmissions($xmlFormId, $since);
        } catch (\Throwable $e) {
            Log::channel($logChannel)->error("OdkSyncService — listSubmissions({$xmlFormId}) a échoué", [
                'error' => $e->getMessage(),
            ]);
            return ['processed' => 0, 'skipped' => 0, 'errors' => 1];
        }

        foreach ($submissions as $meta) {
            $instanceId = $meta['instanceId'] ?? null;
            if (! $instanceId) {
                $skipped++;
                continue;
            }

            try {
                $xml = $this->apiClient->getSubmissionXml($xmlFormId, $instanceId);

                Log::info('ODK XML RAW', [
                    'instanceId' => $instanceId,
                    'preview' => substr($xml, 0, 200)
                ]);
                $result = $this->mapper->map($xmlFormId, $formType, $xml);

                if ($result !== null) {
                    $this->apiClient->markSubmissionReviewed($xmlFormId, $instanceId);
                    $processed++;
                } else {
                    $skipped++;
                }
            } catch (\Throwable $e) {
                $errors++;
                Log::channel($logChannel)->error("OdkSyncService — erreur soumission {$instanceId}", [
                    'form_id'   => $xmlFormId,
                    'form_type' => $formType,
                    'error'     => $e->getMessage(),
                ]);
            }
        }

        if ($processed + $skipped + $errors > 0) {
            $this->setCursor($xmlFormId, now());
        }

        return compact('processed', 'skipped', 'errors');
    }

    // ---------------------------------------------------------------
    //  Curseurs de pagination (Redis)
    // ---------------------------------------------------------------

    private function getCursor(string $formId): ?Carbon
    {
        $ts = Cache::get(self::CURSOR_PREFIX . $formId);
        return $ts ? Carbon::createFromTimestamp($ts) : null;
    }

    private function setCursor(string $formId, Carbon $dt): void
    {
        Cache::put(self::CURSOR_PREFIX . $formId, $dt->timestamp, self::CURSOR_TTL);
    }

    /**
     * Réinitialise le curseur d'un formulaire (re-traite tout depuis le début).
     */
    public function resetCursor(string $formId): void
    {
        Cache::forget(self::CURSOR_PREFIX . $formId);
        Log::warning("OdkSyncService — curseur réinitialisé pour {$formId}");
    }
}
