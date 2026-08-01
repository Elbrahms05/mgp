<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Channels;

use App\Domains\Vbg\Enums\VbgChannel;

/**
 * Canal ⑤ - Personne de confiance hors comité
 *
 * Accès limité rôle `personne_confiance_vbg`.
 * Saisie Partie 1 (Annexe 9) uniquement : contexte, consentement survivante.
 * Transmission directe à l'UGP - jamais au CBGP/CCGP.
 *
 * Les données validées par TrustedPersonController sont passées ici
 * pour être normalisées avant ingestion.
 */
class TrustedPersonChannel
{
    /**
     * Normalise les données validées du formulaire Partie 1.
     *
     * @param  array<string, mixed> $validated  Données validées par TrustedPersonController
     * @param  int                  $userId     ID de la personne de confiance (received_by = trusted_person_id)
     * @return array<string, mixed>
     */
    public function normalize(array $validated, int $userId): array
    {
        return [
            'channel'            => VbgChannel::TRUSTED_PERSON,
            'is_anonymous'       => (bool) ($validated['is_anonymous'] ?? false),
            'consent_given'      => (bool) ($validated['consent_given'] ?? false),
            'caller_phone'       => $validated['caller_phone'] ?? null,
            'incident_date'      => $validated['incident_date'] ?? null,
            'incident_zone'      => $validated['incident_zone'] ?? null,
            'survivor_age_group' => $validated['survivor_age_group'] ?? null,
            'author_link'        => $validated['author_link'] ?? null,
            'classification'     => $validated['classification'] ?? null,
            'site_id'            => $validated['site_id'] ?? null,
            'trusted_person_id'  => $userId,
            'received_by'        => $userId,
        ];
    }
}
