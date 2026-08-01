<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg\DirectReport;

use App\Domains\Vbg\Channels\WhatsAppChannel;
use App\Domains\Vbg\Services\DirectReportIngestionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les messages WhatsApp via WhatsApp Business API (Meta Cloud API v18+).
 * GET  → vérification challenge Meta (enregistrement du webhook)
 * POST → réception de messages entrants
 * La réponse ne mentionne JAMAIS "VBG", "violence" ou tout terme sensible.
 */
class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private readonly WhatsAppChannel $channel,
        private readonly DirectReportIngestionService $ingestion,
    ) {}

    public function __invoke(Request $request): JsonResponse|\Illuminate\Http\Response
    {
        if ($request->isMethod('GET')) {
            return $this->handleVerification($request);
        }

        return $this->handleIncomingMessage($request);
    }

    private function handleVerification(Request $request): \Illuminate\Http\Response
    {
        $mode      = $request->query('hub_mode');
        $token     = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');
        $expected  = config('vbg.whatsapp_verify_token') ?? env('WHATSAPP_VERIFY_TOKEN', '');

        if ($mode === 'subscribe' && $token === $expected) {
            return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403);
    }

    private function handleIncomingMessage(Request $request): JsonResponse
    {
        if (! $this->verifySignature($request)) {
            Log::channel('vbg')->warning('WhatsApp webhook: signature verification failed', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $payload    = $request->json()->all();
            $normalized = $this->channel->normalize($payload);

            if ($normalized === null) {
                // Notification de statut (delivered, read) - 200 immédiat requis par Meta
                return response()->json(['status' => 'ok']);
            }

            $report = $this->ingestion->ingest($normalized);

            // La réponse 200 est envoyée à Meta ; l'envoi du message de réponse WhatsApp
            // à l'utilisateur se fait via l'API Graph (phase 2 - job asynchrone)
            Log::channel('vbg')->info('WhatsApp report created', ['code' => $report->code]);

            return response()->json(['status' => 'received']);

        } catch (\Throwable $e) {
            Log::channel('vbg')->error('WhatsApp webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function verifySignature(Request $request): bool
    {
        $appSecret = config('vbg.whatsapp_app_secret') ?? env('WHATSAPP_APP_SECRET', '');
        $signature = $request->header('X-Hub-Signature-256', '');
        $body      = $request->getContent();

        if (empty($appSecret) || empty($signature)) {
            return app()->isLocal();
        }

        return hash_equals('sha256=' . hash_hmac('sha256', $body, $appSecret), $signature);
    }
}
