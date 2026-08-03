<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Domains\Complaint\Models\Complaint;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Consultation de l'état d'avancement d'une plainte par son numéro (remontée d'état, EF-01).
 *
 * Ne renvoie que des données non nominatives : numéro, statut, niveau, échéance. Aucune
 * plainte sensible (VBG) n'est exposée ici - le suivi VBG passe par le circuit dédié.
 */
class ComplaintStatusController extends Controller
{
    public function show(Request $request, string $registrationNumber): JsonResponse
    {
        $complaint = Complaint::query()
            ->where('registration_number', $registrationNumber)
            ->whereHas('complaintType', fn ($q) => $q->where('is_sensitive', false))
            ->first();

        if ($complaint === null) {
            return response()->json(['message' => 'Plainte introuvable.'], 404);
        }

        return response()->json([
            'registration_number' => $complaint->registration_number,
            'status'              => $complaint->status->value,
            'status_label'        => $complaint->status->label(),
            'current_level'       => $complaint->current_level?->value,
            'sla_deadline'        => $complaint->sla_deadline?->toIso8601String(),
            'closed_at'           => $complaint->closed_at?->toIso8601String(),
        ]);
    }
}
