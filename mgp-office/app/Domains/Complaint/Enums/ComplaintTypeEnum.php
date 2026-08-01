<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Enums;

enum ComplaintTypeEnum: int
{
    case TYPE_1 = 1;
    case TYPE_2 = 2;
    case TYPE_3 = 3;
    case TYPE_4 = 4;
    case TYPE_5 = 5;
    case TYPE_6 = 6;
    case TYPE_7 = 7;
    case TYPE_8 = 8;

    public function label(): string
    {
        return match ($this) {
            self::TYPE_1 => 'Inaccessibilité aux informations liées au Projet',
            self::TYPE_2 => 'Plaintes liées à l\'exécution des marchés',
            self::TYPE_3 => 'Plaintes liées aux travaux de réhabilitation',
            self::TYPE_4 => 'Plaintes liées aux conditions de travail',
            self::TYPE_5 => 'Plaintes liées aux pertes/affectation biens physiques',
            self::TYPE_6 => 'Plaintes liées à l\'octroi d\'équipements',
            self::TYPE_7 => 'Plaintes liées aux formations',
            self::TYPE_8 => 'Plaintes VBG/EAS/HS et VCE',
        };
    }

    public function isSensitive(): bool
    {
        return $this === self::TYPE_8;
    }
}
