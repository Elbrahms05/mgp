<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Channels;

use App\Domains\Vbg\Enums\VbgChannel;

/**
 * Canal ② - SMS short code 3xxx
 *
 * Deux formats acceptés :
 *   Format libre    : "PACIPA [message libre]"
 *   Format structuré: "VBG [type:1-6] [quand:1-4] [rappel:OUI/NON]"
 *
 * Mot-clé "ANONYME" = numéro non stocké.
 *
 * Payload gateway (normalisé Africa's Talking / Twilio / Infobip) :
 * {
 *   "from":    "+22790000000",     // ou "msisdn"
 *   "text":    "PACIPA ...",       // ou "message"
 *   "msgId":   "ATXid_...",        // ou "id"
 *   "keyword": "PACIPA"
 * }
 */
class SmsChannel
{
    private const INCIDENT_TYPES = [
        '1' => 'violence_physique',
        '2' => 'violence_sexuelle',
        '3' => 'mariage_force',
        '4' => 'harcelement',
        '5' => 'autre',
        '6' => null,
    ];

    /**
     * Parse le payload SMS et retourne les données normalisées.
     *
     * @param  array<string, mixed> $payload
     * @return array{normalized: array<string, mixed>, is_structured: bool}
     */
    public function normalize(array $payload): array
    {
        $from = $payload['from']    ?? $payload['msisdn']  ?? null;
        $text = $payload['text']    ?? $payload['message'] ?? '';
        $text = trim($text);

        $isAnonymous  = stripos($text, 'ANONYME') !== false;
        $isStructured = $this->isStructured($text);

        $data = [
            'channel'        => VbgChannel::SMS,
            'is_anonymous'   => $isAnonymous,
            'consent_given'  => true,
            'caller_phone'   => ($isAnonymous || ! $from) ? null : $from,
            'raw_sms_content'=> mb_substr($text, 0, 1000),
            'incident_zone'  => null,
        ];

        if ($isStructured) {
            $data = array_merge($data, $this->parseStructured($text));
        }

        return ['normalized' => $data, 'is_structured' => $isStructured];
    }

    /**
     * Réponse SMS neutre.
     * CRITIQUE : jamais "VBG", "violence", "plainte".
     */
    public function response(string $code, string $hotline = ''): string
    {
        $hotlinePart = $hotline ? " Urgence : {$hotline}." : '';
        return "Merci pour votre message au service PACIPA. Code : {$code}.{$hotlinePart}";
    }

    private function isStructured(string $text): bool
    {
        return (bool) preg_match('/^VBG\s+\d\s+\d(\s+(OUI|NON))?/i', $text);
    }

    /**
     * Parse "VBG [type] [quand] [OUI|NON]"
     * @return array<string, mixed>
     */
    private function parseStructured(string $text): array
    {
        // "VBG 2 1 OUI"
        preg_match('/^VBG\s+(\d)\s+(\d)(\s+(OUI|NON))?/i', $text, $m);

        $typeKey   = $m[1] ?? null;
        $wantsCb   = strtoupper(trim($m[4] ?? '')) === 'OUI';

        return [
            'incident_zone' => self::INCIDENT_TYPES[$typeKey] ?? null,
            // Si "NON" ou pas de préférence → anonyme
            'is_anonymous'  => ! $wantsCb,
        ];
    }
}
