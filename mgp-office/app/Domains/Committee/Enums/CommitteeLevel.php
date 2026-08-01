<?php

declare(strict_types=1);

namespace App\Domains\Committee\Enums;

enum CommitteeLevel: string
{
    case CBGP = 'cbgp';
    case CCGP = 'ccgp';
    case CNGP = 'cngp';

    public function label(): string
    {
        return match ($this) {
            self::CBGP => 'CBGP - Comité de Base de Gestion des Plaintes',
            self::CCGP => 'CCGP - Comité Communal de Gestion des Plaintes',
            self::CNGP => 'CNGP - Comité National de Gestion des Plaintes',
        };
    }

    public function examDaysKey(): string
    {
        return match ($this) {
            self::CBGP => 'cbgp_exam_days',
            self::CCGP => 'ccgp_exam_days',
            self::CNGP => 'cngp_exam_days',
        };
    }

    public function investigationDaysKey(): string
    {
        return match ($this) {
            self::CBGP => 'cbgp_investigation_days',
            self::CCGP => 'ccgp_investigation_days',
            self::CNGP => 'cngp_investigation_days',
        };
    }
}
