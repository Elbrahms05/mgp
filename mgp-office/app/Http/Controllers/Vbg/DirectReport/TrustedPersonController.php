<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vbg\DirectReport;

use App\Domains\Vbg\Channels\TrustedPersonChannel;
use App\Domains\Vbg\Enums\ReferralServiceType;
use App\Domains\Vbg\Enums\VbgAuthorLink;
use App\Domains\Vbg\Enums\GbvimsClassification;
use App\Domains\Vbg\Services\DirectReportIngestionService;
use App\Domains\Vbg\Services\TrustedPersonComplianceService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

/**
 * API pour les Personnes de confiance VBG (app mobile / Sanctum).
 * Saisie Partie 1 du formulaire F03 (Annexe 9) uniquement.
 * Transmission directe à l'UGP - jamais au CBGP/CCGP.
 * Accès : permission vbg.receive_direct (rôle personne_confiance_vbg).
 */
class TrustedPersonController extends Controller
{
    public function __construct(
        private readonly TrustedPersonChannel $channel,
        private readonly DirectReportIngestionService $ingestion,
        private readonly TrustedPersonComplianceService $compliance,
    ) {}

    /**
     * Enregistre un signalement VBG (Partie 1 Annexe 9).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'caller_phone'       => ['nullable', 'string', 'max:20'],
            'is_anonymous'       => ['boolean'],
            'consent_given'      => ['required', 'boolean'],
            'incident_date'      => ['required', 'date', 'before_or_equal:today'],
            'incident_zone'      => ['nullable', 'string', 'max:255'],
            'survivor_age_group' => ['nullable', 'string', 'in:girl_lt18,woman_gte18,boy_lt18,man_gte18'],
            'author_link'        => ['nullable', new Enum(VbgAuthorLink::class)],
            'classification'     => ['nullable', new Enum(GbvimsClassification::class)],
            'site_id'            => ['nullable', 'exists:sites,id'],
            'conflict_declared'  => ['nullable', 'boolean'],
        ]);

        $trustedPerson = $request->user();

        // 2.5 - Recyclage obligatoire : une formation à jour (< 6 mois) est requise.
        if (! $this->compliance->isTrainingValid($trustedPerson)) {
            return response()->json([
                'message' => "Votre formation de personne de confiance n'est plus à jour. "
                    . "Un recyclage est requis avant de pouvoir enregistrer un signalement.",
            ], 403);
        }

        // 2.5 - Contrôle automatique de conflit d'intérêts.
        if ($this->compliance->detectConflict($validated, $trustedPerson)) {
            return response()->json([
                'message' => "Conflit d'intérêts détecté : vous ne pouvez pas traiter ce cas. "
                    . "Orientez la personne vers une autre personne de confiance ou directement vers l'UGP.",
            ], 422);
        }

        $normalized = $this->channel->normalize($validated, $trustedPerson->id);
        $report     = $this->ingestion->ingest($normalized);

        return response()->json([
            'code'    => $report->code,
            'message' => "Signalement transmis à l'équipe de coordination. Réf : {$report->code}",
        ], 201);
    }

    /**
     * Liste des types de services de référencement disponibles.
     * Utilisé par l'app mobile pour pré-remplir le formulaire.
     */
    public function services(): JsonResponse
    {
        $services = collect(ReferralServiceType::cases())->map(fn ($type) => [
            'value' => $type->value,
            'label' => $type->label(),
        ]);

        return response()->json(['data' => $services]);
    }
}
