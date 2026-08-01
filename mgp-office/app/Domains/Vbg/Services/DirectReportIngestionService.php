<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Enums\VbgChannel;
use App\Domains\Vbg\Models\VbgReport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Point d'entrée unifié pour tous les canaux directs VBG.
 * Garantit : chiffrement immédiat, génération de code, notification ESIRT.
 * Utilisé par les 4 webhook/API controllers pour éviter la duplication.
 */
class DirectReportIngestionService
{
    public function __construct(
        private readonly VbgEncryptionService $encryption,
        private readonly WorldBankNotificationService $worldBank,
    ) {}

    /**
     * Crée un VbgReport à partir d'un payload normalisé par un Channel.
     *
     * @param  array{
     *   channel: VbgChannel,
     *   caller_phone?: string|null,
     *   is_anonymous?: bool,
     *   consent_given?: bool,
     *   incident_date?: string|null,
     *   incident_zone?: string|null,
     *   survivor_age_group?: string|null,
     *   author_link?: string|null,
     *   classification?: string|null,
     *   ussd_session_id?: string|null,
     *   raw_sms_content?: string|null,
     *   site_id?: int|null,
     *   trusted_person_id?: int|null,
     *   received_by?: int|null,
     * } $payload
     */
    public function ingest(array $payload): VbgReport
    {
        return DB::transaction(function () use ($payload): VbgReport {
            $channel     = $payload['channel'];
            $callerPhone = $payload['caller_phone'] ?? null;

            // Chiffrement immédiat du numéro avant toute persistance
            $encryptedPhone = null;
            if ($callerPhone && ! ($payload['is_anonymous'] ?? false)) {
                $encryptedPhone = $this->encryption->encryptPhone($callerPhone);
            }

            $code   = $this->generateCode();
            $report = VbgReport::create([
                'code'                   => $code,
                'channel'                => $channel,
                'is_anonymous'           => (bool) ($payload['is_anonymous'] ?? true),
                'consent_given'          => (bool) ($payload['consent_given'] ?? true),
                'consent_given_at'       => ($payload['consent_given'] ?? true) ? now() : null,
                'caller_phone_encrypted' => $encryptedPhone,
                'incident_date'          => $payload['incident_date'] ?? null,
                'incident_zone'          => $payload['incident_zone'] ?? null,
                'survivor_age_group'     => $payload['survivor_age_group'] ?? null,
                'author_link'            => $payload['author_link'] ?? null,
                'classification'         => $payload['classification'] ?? null,
                'ussd_session_id'        => $payload['ussd_session_id'] ?? null,
                'raw_sms_content'        => isset($payload['raw_sms_content'])
                    ? mb_substr($payload['raw_sms_content'], 0, 1000)
                    : null,
                'site_id'                => $payload['site_id'] ?? null,
                'trusted_person_id'      => $payload['trusted_person_id'] ?? null,
                'received_by'            => $payload['received_by'] ?? null,
                'status'                 => 'new',
            ]);

            // Notification UGP → Banque mondiale dans les 24h (ESIRT)
            $this->worldBank->notifyEsirt($report);

            Log::channel('vbg')->info('VBG report ingested', [
                'code'    => $code,
                'channel' => $channel->value,
            ]);

            return $report;
        });
    }

    /**
     * Génère un code séquentiel par année : VBG-YYYY-NNNNN.
     * Verrou transactionnel pour éviter les doublons sous charge concurrente.
     */
    private function generateCode(): string
    {
        $year = now()->year;
        $seq  = VbgReport::whereYear('created_at', $year)->lockForUpdate()->count() + 1;
        return 'VBG-' . $year . '-' . str_pad((string) $seq, 5, '0', STR_PAD_LEFT);
    }
}
