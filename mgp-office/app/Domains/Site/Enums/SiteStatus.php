<?php

declare(strict_types=1);

namespace App\Domains\Site\Enums;

enum SiteStatus: string
{
    case PLANNED     = 'planned';
    case IN_PROGRESS = 'in_progress';
    case SUSPENDED   = 'suspended';
    case COMPLETED   = 'completed';
    case CLOSED      = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::PLANNED     => 'Planifié',
            self::IN_PROGRESS => 'En cours',
            self::SUSPENDED   => 'Suspendu',
            self::COMPLETED   => 'Terminé',
            self::CLOSED      => 'Clos',
        };
    }
}
