<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Enums;

enum VbgChannel: string
{
    case USSD           = 'ussd';
    case SMS            = 'sms';
    case WHATSAPP       = 'whatsapp';
    case VOICE_LINE     = 'voice_line';
    case TRUSTED_PERSON = 'trusted_person';
    case POINT_FOCAL    = 'point_focal';
    case WEB            = 'web';

    public function label(): string
    {
        return match ($this) {
            self::USSD           => 'USSD (*xxx#)',
            self::SMS            => 'SMS (short code)',
            self::WHATSAPP       => 'WhatsApp Bot',
            self::VOICE_LINE     => 'Ligne Verte (appel vocal)',
            self::TRUSTED_PERSON => 'Personne de confiance',
            self::POINT_FOCAL    => 'Point focal VBG',
            self::WEB            => 'Formulaire Web',
        };
    }

    public function isDirectChannel(): bool
    {
        return in_array($this, [
            self::USSD, self::SMS, self::WHATSAPP,
            self::VOICE_LINE, self::TRUSTED_PERSON,
        ], strict: true);
    }
}
