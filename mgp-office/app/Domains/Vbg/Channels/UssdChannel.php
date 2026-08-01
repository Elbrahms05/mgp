<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Channels;

use App\Domains\Vbg\Enums\VbgChannel;

/**
 * Canal ① - USSD *xxx#
 *
 * Le gateway USSD envoie un payload JSON avec la session et les réponses.
 * Le menu USSD collecte : type incident (1-6), période, lien projet, rappel.
 * La réponse au gateway ne mentionne JAMAIS "VBG" ni "violence".
 *
 * Payload attendu :
 * {
 *   "sessionId":   "ABC123",
 *   "msisdn":      "+22790000000",
 *   "serviceCode": "*384#",
 *   "text":        "1*2*3*1",   ← réponses séquentielles au menu (séparées par *)
 *   "networkCode": "62001"
 * }
 */
class UssdChannel
{
    /** Mapping réponse menu → incident_zone (6e option = ne pas préciser) */
    private const INCIDENT_TYPES = [
        '1' => 'violence_physique',
        '2' => 'violence_sexuelle',
        '3' => 'mariage_force',
        '4' => 'harcelement',
        '5' => 'autre',
        '6' => null, // "Je préfère ne pas dire"
    ];

    private const PERIOD_MAP = [
        '1' => 'Aujourd\'hui',
        '2' => 'Cette semaine',
        '3' => 'Ce mois',
        '4' => 'Avant ce mois',
    ];

    /**
     * Parse le payload USSD et retourne les données normalisées.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function normalize(array $payload): array
    {
        $sessionId = $payload['sessionId']  ?? null;
        $msisdn    = $payload['msisdn']     ?? null;
        $text      = $payload['text']       ?? '';

        // text = "1*2*3*1" → [langue, type, quand, lien_projet, rappel]
        $steps = array_filter(explode('*', $text), fn ($v) => $v !== '');
        $steps = array_values($steps);

        $typeStep    = $steps[1] ?? null;   // réponse étape 2 (type incident)
        $periodStep  = $steps[2] ?? null;   // réponse étape 3 (quand)
        $callbackStep= $steps[4] ?? null;   // réponse étape 5 (rappel)

        $isAnonymous  = ($callbackStep === '3'); // "Non, rester anonyme"
        $wantsCallback= in_array($callbackStep, ['1', '2'], true);
        $incidentZone = self::INCIDENT_TYPES[$typeStep] ?? null;
        $period       = self::PERIOD_MAP[$periodStep]   ?? null;

        return [
            'channel'         => VbgChannel::USSD,
            'is_anonymous'    => $isAnonymous,
            'consent_given'   => true, // l'appel USSD vaut consentement
            'caller_phone'    => ($wantsCallback && ! $isAnonymous) ? $msisdn : null,
            'incident_zone'   => $incidentZone,
            'ussd_session_id' => $sessionId,
            'raw_sms_content' => $period ? "Période : {$period}" : null,
        ];
    }

    /**
     * Formate la réponse USSD finale.
     * CRITIQUE : jamais "VBG", "violence", "plainte".
     */
    public function response(string $code, bool $wantsCallback): array
    {
        $message = $wantsCallback
            ? "Merci. Code : {$code}. Un agent vous contactera sous 24h. Ne communiquez ce code à personne."
            : "Merci. Votre message a été transmis. Réf : {$code}. Vous pouvez suivre via le menu 3.";

        return [
            'sessionState' => 'end',
            'message'      => $message,
        ];
    }
}
