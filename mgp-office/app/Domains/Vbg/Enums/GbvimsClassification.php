<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Enums;

enum GbvimsClassification: string
{
    case RAPE                    = 'rape';
    case SEXUAL_ASSAULT          = 'sexual_assault';
    case PHYSICAL_ASSAULT        = 'physical_assault';
    case PSYCHOLOGICAL_EMOTIONAL = 'psychological_emotional';
    case FORCED_MARRIAGE         = 'forced_marriage';
    case DENIAL_OF_RESOURCES     = 'denial_of_resources';
    case NO_INCIDENT             = 'no_incident';

    public function label(): string
    {
        return match ($this) {
            self::RAPE                    => 'Viol',
            self::SEXUAL_ASSAULT          => 'Agression sexuelle',
            self::PHYSICAL_ASSAULT        => 'Agression physique',
            self::PSYCHOLOGICAL_EMOTIONAL => 'Violence psychologique/émotionnelle',
            self::FORCED_MARRIAGE         => 'Mariage forcé/précoce',
            self::DENIAL_OF_RESOURCES     => 'Déni de services, ressources ou opportunités',
            self::NO_INCIDENT             => 'Aucun incident VBG vérifié',
        };
    }
}
