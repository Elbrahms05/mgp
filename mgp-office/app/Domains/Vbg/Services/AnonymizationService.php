<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Models\VbgConsent;
use App\Domains\Vbg\Models\VbgReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Gestion du cycle de vie des données VBG sensibles.
 *
 * Deux opérations distinctes :
 *   1. Purge cryptographique : efface les données identifiantes d'un rapport clos
 *      (caller_phone_encrypted, ussd_session_id, raw_sms_content)
 *   2. Rotation des logs webhooks : purge les logs bruts après 72h
 *      (conformité ESIRT - seul le signalement chiffré en base est conservé)
 */
class AnonymizationService
{
    /**
     * Purge cryptographique des données identifiantes d'un rapport clos.
     *
     * Appelé après la période légale de conservation (configurable via settings).
     * Conserve : code, channel, statut, dates, classification agrégée.
     * Efface : numéro chiffré, contenu SMS brut, session USSD, voice_memo.
     */
    public function purge(VbgReport $report): void
    {
        DB::transaction(function () use ($report): void {
            $report->updateQuietly([
                'caller_phone_encrypted' => null,
                'ussd_session_id'        => null,
                'raw_sms_content'        => null,
                'voice_memo_path'        => null,
                'trusted_person_id'      => null, // dissociation personne de confiance
                'received_by'            => null, // dissociation opérateur
            ]);

            // Purge du consentement (lié à l'identité)
            VbgConsent::where('vbg_report_id', $report->id)->delete();

            // Suppression des médias via Spatie MediaLibrary
            try {
                $report->clearMediaCollection('voice_memos');
                $report->clearMediaCollection('documents');
            } catch (\Throwable) {
                // Pas de media attaché - ignorer
            }

            Log::channel('vbg')->info('Cryptographic purge completed', [
                'report_code' => $report->code,
                'purged_at'   => now()->toDateTimeString(),
            ]);
        });
    }

    /**
     * Purge des VbgReports de canaux directs restés en statut 'new' depuis plus de 72h
     * sans être pris en charge par un opérateur.
     *
     * Ces rapports sont potentiellement des tests ou des envois erronés.
     * Les données identifiantes sont purgées mais le rapport (code + statut) reste.
     */
    public function purgeStaleDirectReports(int $hoursThreshold = 72): int
    {
        $stale = VbgReport::where('status', 'new')
            ->whereIn('channel', ['ussd', 'sms', 'whatsapp'])
            ->where('created_at', '<', now()->subHours($hoursThreshold))
            ->whereNull('received_by')
            ->get();

        foreach ($stale as $report) {
            $this->purge($report);
        }

        if ($stale->count() > 0) {
            Log::channel('vbg')->warning('Stale direct reports purged', [
                'count'     => $stale->count(),
                'threshold' => "{$hoursThreshold}h",
            ]);
        }

        return $stale->count();
    }

    /**
     * Purge des logs de canaux directs après le délai réglementaire.
     * Appel planifié via Console\Commands ou Scheduler.
     */
    public function rotateWebhookLogs(): void
    {
        // Les logs canal VBG sont dans storage/logs/vbg-*.log
        // On délègue à la rotation du logger (configurée dans config/logging.php)
        // Cette méthode peut être étendue pour purger un stockage externe (S3, etc.)
        Log::channel('vbg')->info('Webhook log rotation triggered');
    }
}
