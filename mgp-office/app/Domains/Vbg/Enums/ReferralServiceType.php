<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Enums;

enum ReferralServiceType: string
{
    case MEDICAL       = 'medical';
    case PSYCHOSOCIAL  = 'psychosocial';
    case LEGAL         = 'legal';
    case SECURITY      = 'security';
    case OTHER         = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MEDICAL      => 'Prise en charge médicale',
            self::PSYCHOSOCIAL => 'Prise en charge psychosociale',
            self::LEGAL        => 'Aide juridique',
            self::SECURITY     => 'Sécurité (police/gendarmerie)',
            self::OTHER        => 'Autres services',
        };
    }
}
