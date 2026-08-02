<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Services;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Settings\SlaSettings;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SlaMonitorService
{
    public function __construct(
        private readonly SlaSettings $settings,
    ) {}

    /**
     * Calcule la date d'échéance SLA pour un niveau donné (ou le niveau courant).
     */
    public function computeDeadline(Complaint $complaint, ?CommitteeLevel $level = null, ?Carbon $from = null): Carbon
    {
        $level ??= $complaint->current_level ?? CommitteeLevel::CBGP;

        $days = match ($level) {
            CommitteeLevel::CBGP => $this->settings->cbgp_exam_days + $this->settings->cbgp_investigation_days,
            CommitteeLevel::CCGP => $this->settings->ccgp_exam_days + $this->settings->ccgp_investigation_days,
            CommitteeLevel::CNGP => $this->settings->cngp_exam_days + $this->settings->cngp_investigation_days,
        };

        // Base = date de réception (saisie différée papier, EF-11) sinon maintenant.
        $base = $from ?? $complaint->received_at ?? now();

        return $base->copy()->addDays($days);
    }

    public function isBreached(Complaint $complaint): bool
    {
        if ($complaint->status->isTerminal()) {
            return false;
        }

        return $complaint->sla_deadline !== null && $complaint->sla_deadline->isPast();
    }

    /**
     * Heures restantes avant dépassement SLA (négatif si déjà dépassé).
     */
    public function hoursRemaining(Complaint $complaint): float
    {
        if ($complaint->sla_deadline === null) {
            return PHP_FLOAT_MAX;
        }

        return now()->diffInHours($complaint->sla_deadline, false);
    }

    /**
     * Retourne les plaintes actives dont le SLA est dépassé.
     */
    public function getBreachedComplaints(): Collection
    {
        return Complaint::whereNotIn('status', [
            ComplaintStatus::CLOSED->value,
            ComplaintStatus::REJECTED->value,
            ComplaintStatus::RESOLVED->value,
        ])
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '<', now())
            ->with(['complaintType', 'site'])
            ->orderBy('sla_deadline')
            ->get();
    }

    /**
     * Retourne les plaintes proches du dépassement SLA (dans alert_threshold_ratio).
     */
    public function getAtRiskComplaints(): Collection
    {
        // Alerte dans les 24h suivantes
        return Complaint::whereNotIn('status', [
            ComplaintStatus::CLOSED->value,
            ComplaintStatus::REJECTED->value,
            ComplaintStatus::RESOLVED->value,
        ])
            ->whereNotNull('sla_deadline')
            ->where('sla_deadline', '>=', now())
            ->where('sla_deadline', '<=', now()->addHours(24))
            ->with(['complaintType', 'site'])
            ->orderBy('sla_deadline')
            ->get();
    }
}
