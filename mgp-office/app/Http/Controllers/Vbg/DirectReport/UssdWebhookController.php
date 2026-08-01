<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg\DirectReport;

use App\Domains\Vbg\Channels\UssdChannel;
use App\Domains\Vbg\Services\DirectReportIngestionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Reçoit les callbacks du gateway USSD (*xxx#).
 * Authentification : HMAC-SHA256 sur le corps de la requête.
 * La réponse ne mentionne JAMAIS "VBG", "violence" ou tout terme sensible.
 */
class UssdWebhookController extends Controller
{
    public function __construct(
        private readonly UssdChannel $channel,
        private readonly DirectReportIngestionService $ingestion,
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        if (! $this->verifyHmac($request)) {
            Log::channel('vbg')->warning('USSD webhook: HMAC verification failed', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        try {
            $payload = $request->json()->all();

            if (empty($payload['sessionId']) || empty($payload['msisdn'])) {
                return response()->json(['message' => 'Bad Request'], 400);
            }

            $normalized = $this->channel->normalize($payload);
            $report     = $this->ingestion->ingest($normalized);

            $wantsCallback = isset($normalized['caller_phone']);
            return response()->json($this->channel->response($report->code, $wantsCallback));

        } catch (\Throwable $e) {
            Log::channel('vbg')->error('USSD webhook error', ['error' => $e->getMessage()]);
            return response()->json([
                'sessionState' => 'end',
                'message'      => 'Service temporairement indisponible.',
            ], 500);
        }
    }

    private function verifyHmac(Request $request): bool
    {
        $secret    = config('vbg.webhook_secret') ?? env('VBG_WEBHOOK_SECRET', '');
        $signature = $request->header('X-USSD-Signature', '');
        $body      = $request->getContent();

        if (empty($secret) || empty($signature)) {
            return app()->isLocal();
        }

        return hash_equals('sha256=' . hash_hmac('sha256', $body, $secret), $signature);
    }
}
