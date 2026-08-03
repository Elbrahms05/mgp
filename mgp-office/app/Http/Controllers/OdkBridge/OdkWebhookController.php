<?php

declare(strict_types=1);

namespace App\Http\Controllers\OdkBridge;

use App\Domains\OdkBridge\Services\OdkSyncService;
use App\Http\Controllers\Controller;
use App\Settings\OdkSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les notifications push d'ODK Central lorsqu'une nouvelle
 * soumission est disponible, puis déclenche une sync ciblée.
 *
 * Sécurité : vérification HMAC-SHA256 de la signature dans X-ODK-Signature.
 * ODK Central envoie : POST /webhooks/odk/submission
 * Body JSON : { "formId": "pacipa_f01", "instanceId": "uuid..." }
 */
class OdkWebhookController extends Controller
{
    public function __construct(
        private readonly OdkSyncService $syncService,
        private readonly OdkSettings    $odkSettings,
    ) {}

    public function submission(Request $request): Response
    {
        // Vérification HMAC
        if (! $this->isValidSignature($request)) {
            Log::warning('OdkWebhookController - signature invalide', [
                'ip'     => $request->ip(),
                'header' => $request->header('X-ODK-Signature', '-'),
            ]);
            return response('Unauthorized', 401);
        }

        $formId = $request->input('formId');

        if (empty($formId)) {
            return response('Bad Request: formId manquant', 400);
        }

        // Sync ciblée sur le formulaire reçu - lookup en DB pour connaître le form_type
        dispatch(function () use ($formId): void {
            $this->syncService->syncByFormId($formId);
        })->afterResponse();

        return response('', 200);
    }

    private function isValidSignature(Request $request): bool
    {
        $secret = $this->odkSettings->webhook_secret;

        if (empty($secret)) {
            // Pas de secret configuré → on accepte (dev/staging uniquement)
            Log::warning('OdkWebhookController - webhook_secret non configuré');
            return true;
        }

        $signature = $request->header('X-ODK-Signature', '');
        $expected  = 'sha256=' . hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
