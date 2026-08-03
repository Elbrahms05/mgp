<?php

declare(strict_types=1);

namespace App\Http\Controllers\OdkBridge;

use App\Domains\OdkBridge\Services\OdkSyncService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Déclenche une synchronisation ODK manuelle depuis le back-office.
 * Réservé aux utilisateurs ayant la permission forms.manage.
 */
class OdkManualSyncController extends Controller
{
    public function __construct(
        private readonly OdkSyncService $syncService,
    ) {}

    /**
     * POST /odk/sync
     * Déclenche la sync non-VBG (ou --all si paramètre inclus).
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $result    = $this->syncService->sync();
            $vbgResult = ['processed' => 0, 'skipped' => 0, 'errors' => 0];

            // Sync VBG uniquement si l'utilisateur a aussi la permission vbg.receive
            if ($request->user()->can('vbg.receive')) {
                $vbgResult = $this->syncService->syncVbg();
            }

            $total = [
                'processed' => $result['processed'] + $vbgResult['processed'],
                'skipped'   => $result['skipped']   + $vbgResult['skipped'],
                'errors'    => $result['errors']    + $vbgResult['errors'],
            ];

            Log::info('OdkManualSyncController - sync manuelle', [
                'user'   => $request->user()->id,
                'result' => $total,
            ]);

            $message = $total['processed'] > 0
                ? "{$total['processed']} soumission(s) importée(s)."
                : "Aucune nouvelle soumission.";

            if ($total['errors'] > 0) {
                $message .= " {$total['errors']} erreur(s) - voir les logs.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'result'  => $total,
            ]);
        } catch (\Throwable $e) {
            Log::error('OdkManualSyncController - échec', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur ODK Central : ' . $e->getMessage(),
            ], 500);
        }
    }
}
