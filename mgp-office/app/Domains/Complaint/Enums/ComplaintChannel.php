<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Enums;

enum ComplaintChannel: string
{
    case IN_PERSON     = 'in_person';
    case PHONE         = 'phone';
    case SMS           = 'sms';
    case WHATSAPP      = 'whatsapp';
    case LETTER        = 'letter';
    case EMAIL         = 'email';
    case COMPLAINT_BOX = 'complaint_box';
    case HOTLINE       = 'hotline';
    case WEB           = 'web';
    case AUTO          = 'auto';

    public function label(): string
    {
        return match ($this) {
            self::IN_PERSON     => 'En personne',
            self::PHONE         => 'Téléphone',
            self::SMS           => 'SMS',
            self::WHATSAPP      => 'WhatsApp',
            self::LETTER        => 'Courrier',
            self::EMAIL         => 'Email',
            self::COMPLAINT_BOX => 'Boîte de réclamation',
            self::HOTLINE       => 'Ligne verte',
            self::WEB           => 'Boîte réclamation Web',
            self::AUTO          => 'Auto-saisine',
        };
    }
}
