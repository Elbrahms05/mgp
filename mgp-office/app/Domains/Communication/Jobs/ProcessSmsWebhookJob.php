<?php

declare(strict_types=1);

namespace App\Domains\Communication\Jobs;

use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintType;
use App\Domains\Complaint\Services\ComplaintWorkflowService;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

/**
 * Traite une soumission SMS entrante (passerelle → back-office), option A de update.md 1.4.
 *
 * La signature HMAC est déjà vérifiée par spatie/laravel-webhook-client avant ce job.
 * Idempotence : chaque message porte un `message_id` ; un même identifiant n'est traité
 * qu'une fois (rejeu passerelle). Un type sensible (VBG) n'est jamais matérialisé ici :
 * il est redirigé vers le circuit dédié.
 *
 * ⚠️ Si la passerelle transmet un contenu chiffré (module mobile `pacipa/sms`), le
 *    déchiffrement compatible `SmsEncryption` doit être branché en tête de handle()
 *    (point d'intégration documenté).
 */
class ProcessSmsWebhookJob extends ProcessWebhookJob
{
    public function handle(): void
    {
        $payload = (array) $this->webhookCall->payload;

        $messageId = (string) ($payload['message_id'] ?? '');

        if ($messageId === '') {
            Log::warning('SMS webhook sans message_id ignoré', ['webhook_call' => $this->webhookCall->id]);
            return;
        }

        // Idempotence : un message déjà traité n'est pas rejoué.
        $cacheKey = "sms_inbound:{$messageId}";
        if (! Cache::add($cacheKey, true, now()->addDays(7))) {
            Log::info('SMS webhook déjà traité (idempotence)', ['message_id' => $messageId]);
            return;
        }

        $type = $this->resolveType($payload);

        if ($type === null) {
            Log::warning('SMS webhook : type de plainte introuvable', ['message_id' => $messageId]);
            return;
        }

        // Les types sensibles ne transitent pas par ce canal (cloisonnement VBG, ER-02).
        if ($type->is_sensitive) {
            Log::notice('SMS webhook : type sensible ignoré sur canal SMS générique', ['message_id' => $messageId]);
            return;
        }

        $workflow = app(ComplaintWorkflowService::class);

        $complaint = $workflow->register([
            'complaint_type_id' => $type->id,
            'description'       => trim((string) ($payload['description'] ?? 'Plainte reçue par SMS')),
            'subject'           => $payload['subject'] ?? 'Soumission SMS',
            'complainant_phone' => $payload['phone'] ?? null,
            'is_anonymous'      => (bool) ($payload['anonymous'] ?? false),
            'channel'           => 'sms',
        ], $this->systemUser());

        Log::info('SMS webhook : plainte créée', [
            'message_id' => $messageId,
            'complaint'  => $complaint->registration_number,
        ]);
    }

    private function resolveType(array $payload): ?ComplaintType
    {
        if (! empty($payload['type_code'])) {
            return ComplaintType::where('code', $payload['type_code'])->first();
        }

        if (! empty($payload['complaint_type_id'])) {
            return ComplaintType::find($payload['complaint_type_id']);
        }

        return ComplaintType::where('is_sensitive', false)->orderBy('sort_order')->first();
    }

    /**
     * Compte technique attribué comme « enregistreur » des soumissions SMS automatiques.
     */
    private function systemUser(): User
    {
        return User::firstOrCreate(
            ['email' => 'sms-gateway@pacipa.local'],
            [
                'name'      => 'Passerelle SMS',
                'password'  => bcrypt(str()->random(40)),
                'is_active' => true,
            ],
        );
    }
}
