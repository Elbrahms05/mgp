<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg\DirectReport;

use App\Domains\Vbg\Channels\SmsChannel;
use App\Domains\Vbg\Services\DirectReportIngestionService;
use App\Http\Controllers\Controller;
use App\Settings\SmsSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les SMS entrants via le provider (short code 3xxx).
 * Authentification : HMAC-SHA256 sur le corps de la requête.
 * La réponse ne mentionne JAMAIS "VBG", "violence" ou tout terme sensible.
 */
class SmsWebhookController extends Controller
{
    public function __construct(
        private readonly SmsChannel $channel,
        private readonly DirectReportIngestionService $ingestion,
        private readonly SmsSettings $smsSettings,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->verifyHmac($request)) {
            Log::channel('vbg')->warning('SMS webhook: HMAC verification failed', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $payload = $request->json()->all();
            $from    = $payload['from'] ?? $payload['msisdn'] ?? null;

            if (! $from) {
                return response()->json(['message' => 'Bad Request: missing sender'], 400);
            }

            ['normalized' => $normalized] = $this->channel->normalize($payload);
            $report  = $this->ingestion->ingest($normalized);
            $hotline = $this->smsSettings->hotline_number ?? '';

            return response()->json([
                'status'    => 'received',
                'sms_reply' => $this->channel->response($report->code, $hotline),
            ]);

        } catch (\Throwable $e) {
            Log::channel('vbg')->error('SMS webhook error', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error'], 500);
        }
    }

    private function verifyHmac(Request $request): bool
    {
        $secret    = config('vbg.webhook_secret') ?? env('VBG_WEBHOOK_SECRET', '');
        $signature = $request->header('X-SMS-Signature', '');
        $body      = $request->getContent();

        if (empty($secret) || empty($signature)) {
            return app()->isLocal();
        }

        return hash_equals('sha256=' . hash_hmac('sha256', $body, $secret), $signature);
    }
}
