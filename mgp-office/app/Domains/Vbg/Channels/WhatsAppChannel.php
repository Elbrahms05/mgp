<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Channels;

use App\Domains\Vbg\Enums\VbgChannel;

/**
 * Canal ④ - WhatsApp Bot (+227 xx xx xx xx)
 *
 * Même arbre de décision que le menu USSD, via WhatsApp Business API (Meta Cloud API v18+).
 * Gère : messages texte, boutons interactifs, messages vocaux (voice_memo_path).
 * L'opératrice transcrit les vocaux - JAMAIS par IA.
 * Le bot conseille de supprimer la conversation du téléphone après usage.
 *
 * Payload entrant (Meta Cloud API) :
 * {
 *   "entry": [{ "changes": [{ "value": {
 *     "messages": [{ "from": "22790000000", "type": "text"|"audio"|"interactive",
 *                    "text": {"body": "..."}, "audio": {"id": "...", "mime_type": "..."} }]
 *   }}]}]
 * }
 */
class WhatsAppChannel
{
    /**
     * Extrait et normalise le message entrant du payload Meta.
     *
     * @param  array<string, mixed> $payload
     * @return array<string, mixed>|null  null si payload ignorable (status update, etc.)
     */
    public function normalize(array $payload): ?array
    {
        $message = $payload['entry'][0]['changes'][0]['value']['messages'][0] ?? null;

        if (! $message) {
            return null; // notification de statut (delivered, read) - ignorer
        }

        $from    = $message['from'] ?? null;
        $type    = $message['type'] ?? 'text';
        $text    = $message['text']['body']        ?? null;
        $audioId = $message['audio']['id']         ?? null; // voice memo

        if (! $from) {
            return null;
        }

        return [
            'channel'         => VbgChannel::WHATSAPP,
            'is_anonymous'    => false, // WhatsApp = numéro connu par défaut
            'consent_given'   => true,
            'caller_phone'    => $from,
            'raw_sms_content' => $text ? mb_substr($text, 0, 1000) : null,
            'voice_memo_id'   => $audioId, // ID media Meta - téléchargement différé
        ];
    }

    /**
     * Formate le message de réponse WhatsApp (affiché dans la conversation).
     * CRITIQUE : jamais "VBG", "violence", "plainte".
     * Inclut instruction de suppression de la conversation.
     */
    public function replyText(string $code): string
    {
        return implode("\n", [
            "✓ Message reçu. Réf : {$code}",
            "Un agent du service d'écoute PACIPA vous contactera sous 24h.",
            "",
            "Pour votre sécurité : supprimez cette conversation de WhatsApp",
            "(Maintenez → Supprimer → Pour moi).",
        ]);
    }
}
