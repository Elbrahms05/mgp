<?php

declare(strict_types=1);

namespace App\Domains\Worker\Enums;

enum WorkerComplaintStatus: string
{
    case NEW          = 'new';
    case UNDER_REVIEW = 'under_review';
    case ESCALATED    = 'escalated';
    case RESOLVED     = 'resolved';
    case CLOSED       = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::NEW          => 'Nouveau',
            self::UNDER_REVIEW => 'En examen',
            self::ESCALATED    => 'Escaladé N2',
            self::RESOLVED     => 'Résolu',
            self::CLOSED       => 'Clôturé',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::NEW          => 'bg-primary',
            self::UNDER_REVIEW => 'bg-warning text-dark',
            self::ESCALATED    => 'bg-danger',
            self::RESOLVED     => 'bg-success',
            self::CLOSED       => 'bg-secondary',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CLOSED, self::RESOLVED], strict: true);
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    /** @return self[] */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::NEW          => [self::UNDER_REVIEW],
            self::UNDER_REVIEW => [self::ESCALATED, self::RESOLVED],
            self::ESCALATED    => [self::RESOLVED],
            self::RESOLVED     => [self::CLOSED],
            self::CLOSED       => [],
        };
    }
}
