<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Channels;

use App\Domains\Vbg\Enums\VbgChannel;

/**
 * Canal ③ - Ligne Verte (appel vocal gratuit)
 *
 * Pas de webhook - l'opératrice ONG VBG saisit le formulaire Annexe 9 Partie 1
 * directement dans le back-office (via VbgReportController::create()) pendant l'appel.
 * Appel jamais enregistré sauf consentement explicite de la survivante.
 *
 * Cette classe est utilisée quand l'opératrice crée le rapport via l'UI back-office
 * avec channel = 'voice_line'.
 */
class VoiceLineChannel
{
    /**
     * Normalise les données saisies par l'opératrice pendant l'appel.
     *
     * @param  array<string, mixed> $validated  Données validées du formulaire
     * @param  int                  $operatorId ID de l'opératrice ONG VBG (received_by)
     * @return array<string, mixed>
     */
    public function normalize(array $validated, int $operatorId): array
    {
        return [
            'channel'            => VbgChannel::VOICE_LINE,
            'is_anonymous'       => (bool) ($validated['is_anonymous'] ?? false),
            'consent_given'      => (bool) ($validated['consent_given'] ?? false),
            'caller_phone'       => $validated['caller_phone'] ?? null,
            'incident_date'      => $validated['incident_date'] ?? null,
            'incident_zone'      => $validated['incident_zone'] ?? null,
            'survivor_age_group' => $validated['survivor_age_group'] ?? null,
            'author_link'        => $validated['author_link'] ?? null,
            'classification'     => $validated['classification'] ?? null,
            'site_id'            => $validated['site_id'] ?? null,
            'received_by'        => $operatorId,
        ];
    }
}
