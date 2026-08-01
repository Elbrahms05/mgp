<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Enums;

enum ComplaintStatus: string
{
    case REGISTERED   = 'registered';
    case UNDER_REVIEW = 'under_review';
    case ACCEPTED     = 'accepted';
    case REJECTED     = 'rejected';
    case IN_MEDIATION = 'in_mediation';
    case RESOLVED     = 'resolved';
    case ESCALATED    = 'escalated';
    case CLOSED       = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::REGISTERED   => 'Enregistrée',
            self::UNDER_REVIEW => 'En examen',
            self::ACCEPTED     => 'Recevable',
            self::REJECTED     => 'Rejetée',
            self::IN_MEDIATION => 'En médiation',
            self::RESOLVED     => 'Résolue',
            self::ESCALATED    => 'Escaladée',
            self::CLOSED       => 'Clôturée',
        };
    }

    public function colorClass(): string
    {
        return match ($this) {
            self::REGISTERED   => 'blue',
            self::UNDER_REVIEW => 'orange',
            self::ACCEPTED     => 'teal',
            self::REJECTED     => 'grey',
            self::IN_MEDIATION => 'amber',
            self::RESOLVED     => 'green',
            self::ESCALATED    => 'deep-orange',
            self::CLOSED       => 'blue-grey',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CLOSED, self::REJECTED], strict: true);
    }

    public function canTransitionTo(self $next): bool
    {
        return in_array($next, $this->allowedTransitions(), strict: true);
    }

    /** @return self[] */
    private function allowedTransitions(): array
    {
        return match ($this) {
            self::REGISTERED   => [self::UNDER_REVIEW],
            self::UNDER_REVIEW => [self::ACCEPTED, self::REJECTED],
            self::ACCEPTED     => [self::IN_MEDIATION, self::ESCALATED],
            self::IN_MEDIATION => [self::RESOLVED, self::ESCALATED],
            self::RESOLVED     => [self::CLOSED],
            self::ESCALATED    => [self::UNDER_REVIEW],
            default            => [],
        };
    }
}
