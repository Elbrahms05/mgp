<?php

declare(strict_types=1);

namespace App\Domains\Site\Enums;

enum SiteCategory: int
{
    case COMPONENT_1 = 1;
    case COMPONENT_2 = 2;
    case COMPONENT_3 = 3;
    case COMPONENT_4 = 4;
    case COMPONENT_5 = 5;

    public function label(): string
    {
        return match ($this) {
            self::COMPONENT_1 => 'C1 - Production agricole résiliente et production animale',
            self::COMPONENT_2 => 'C2 - Marchés agricoles et d\'élevage',
            self::COMPONENT_3 => 'C3 - Accès au financement rural',
            self::COMPONENT_4 => 'C4 - Coordination et renforcement des capacités',
            self::COMPONENT_5 => 'C5 - Gestion de projet',
        };
    }
}
