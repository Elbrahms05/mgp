<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Enums;

enum VbgAuthorLink: string
{
    case PROJECT_WORKER   = 'project_worker';
    case SECURITY_FORCES  = 'security_forces';
    case UNKNOWN          = 'unknown';
    case NO_LINK          = 'no_link';

    public function label(): string
    {
        return match ($this) {
            self::PROJECT_WORKER  => 'Personnel entreprise / Associé projet',
            self::SECURITY_FORCES => 'Forces de défense et sécurité (FDS)',
            self::UNKNOWN         => 'Inconnu',
            self::NO_LINK         => 'Individu sans lien avec le projet',
        };
    }
}
