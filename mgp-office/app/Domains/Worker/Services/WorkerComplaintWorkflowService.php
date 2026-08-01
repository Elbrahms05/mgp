<?php

declare(strict_types=1);

namespace App\Domains\Worker\Services;

use App\Domains\Worker\Enums\WorkerComplaintStatus;
use App\Domains\Worker\Models\WorkerComplaint;
use App\Domains\Worker\Models\WorkerComplaintHistory;
use App\Models\User;
use App\Notifications\Worker\WorkerComplaintTransitionNotification;
use App\Settings\SlaSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\Notification;

class WorkerComplaintWorkflowService
{
    public function __construct(
        private readonly SlaSettings $sla,
    ) {}

    // ── Transitions ────────────────────────────────────────────────────────

    /**
     * Démarre l'examen - new → under_review.
     * Calcule la première échéance SLA.
     */
    public function startReview(WorkerComplaint $complaint, User $by, ?string $comment = null): void
    {
        $this->assertTransition($complaint, WorkerComplaintStatus::UNDER_REVIEW);

        $complaint->status       = WorkerComplaintStatus::UNDER_REVIEW;
        $complaint->sla_deadline = $this->computeDeadline($complaint);
        $complaint->save();

        $this->log($complaint, WorkerComplaintStatus::NEW, WorkerComplaintStatus::UNDER_REVIEW, 'start_review', $comment ?? 'Examen démarré', $by);
        $this->notify($complaint, $by, 'start_review');
    }

    /**
     * Escalade N1 → N2.
     * Recalcule l'échéance SLA pour le niveau 2.
     */
    public function escalate(WorkerComplaint $complaint, User $by, string $comment): void
    {
        $this->assertTransition($complaint, WorkerComplaintStatus::ESCALATED);

        $from = $complaint->status;

        $complaint->status       = WorkerComplaintStatus::ESCALATED;
        $complaint->level        = 2;
        $complaint->escalated_at = now();
        $complaint->sla_deadline = $this->computeDeadline($complaint, level: 2);
        $complaint->save();

        $this->log($complaint, $from, WorkerComplaintStatus::ESCALATED, 'escalate', $comment, $by);
        $this->notify($complaint, $by, 'escalate');
    }

    /**
     * Résolution de la plainte.
     */
    public function resolve(WorkerComplaint $complaint, User $by, string $comment): void
    {
        $this->assertTransition($complaint, WorkerComplaintStatus::RESOLVED);

        $from = $complaint->status;

        $complaint->status      = WorkerComplaintStatus::RESOLVED;
        $complaint->resolved_at = now();
        $complaint->save();

        $this->log($complaint, $from, WorkerComplaintStatus::RESOLVED, 'resolve', $comment, $by);
        $this->notify($complaint, $by, 'resolve');
    }

    /**
     * Clôture définitive (après résolution).
     */
    public function close(WorkerComplaint $complaint, User $by, ?string $comment = null): void
    {
        $this->assertTransition($complaint, WorkerComplaintStatus::CLOSED);

        $from = $complaint->status;

        $complaint->status = WorkerComplaintStatus::CLOSED;
        $complaint->save();

        $this->log($complaint, $from, WorkerComplaintStatus::CLOSED, 'close', $comment ?? 'Clôturée', $by);
    }

    /**
     * Auto-escalade système (déclenchée par la commande check-sla).
     * Pas de notification directe - la commande gère les logs.
     */
    public function autoEscalate(WorkerComplaint $complaint, User $system): void
    {
        if (! $complaint->status->canTransitionTo(WorkerComplaintStatus::ESCALATED)) {
            return;
        }

        $from = $complaint->status;

        $complaint->status       = WorkerComplaintStatus::ESCALATED;
        $complaint->level        = 2;
        $complaint->escalated_at = now();
        $complaint->sla_deadline = $this->computeDeadline($complaint, level: 2);
        $complaint->save();

        $this->log(
            $complaint,
            $from,
            WorkerComplaintStatus::ESCALATED,
            'auto_escalate',
            "Auto-escalade système : délai SLA N1 dépassé",
            $system,
        );
    }

    // ── SLA ───────────────────────────────────────────────────────────────

    public function computeDeadline(WorkerComplaint $complaint, ?int $level = null): Carbon
    {
        $level ??= $complaint->level;
        $base   = now();

        if ($complaint->worker_type === 'direct') {
            $days = $level === 1
                ? $this->sla->worker_direct_level1_days
                : $this->sla->worker_direct_level2_days;
            return $base->addDays($days);
        }

        // Contractuel N1 = jours ouvrables
        if ($level === 1) {
            return $this->addWorkingDays($base, $this->sla->worker_contractor_level1_working_days);
        }

        return $base->addDays($this->sla->worker_contractor_level2_days);
    }

    // ── Helpers privés ─────────────────────────────────────────────────────

    private function assertTransition(WorkerComplaint $complaint, WorkerComplaintStatus $to): void
    {
        if (! $complaint->status->canTransitionTo($to)) {
            throw new \DomainException(
                "Transition invalide : {$complaint->status->label()} → {$to->label()} pour {$complaint->registration_number}"
            );
        }
    }

    private function log(
        WorkerComplaint $complaint,
        WorkerComplaintStatus $from,
        WorkerComplaintStatus $to,
        string $action,
        ?string $comment,
        User $by,
    ): void {
        WorkerComplaintHistory::create([
            'worker_complaint_id' => $complaint->id,
            'from_status'         => $from->value,
            'to_status'           => $to->value,
            'action'              => $action,
            'comment'             => $comment,
            'performed_by'        => $by->id,
        ]);
    }

    private function notify(WorkerComplaint $complaint, User $by, string $event): void
    {
        // Notifier les utilisateurs avec la permission workers.manage (sauf l'auteur de la transition)
        $recipients = User::permission('workers.manage')
            ->where('id', '!=', $by->id)
            ->get();

        if ($recipients->isNotEmpty()) {
            Notification::send(
                $recipients,
                new WorkerComplaintTransitionNotification($complaint, $event, $by),
            );
        }
    }

    /** Ajoute N jours ouvrables (hors week-end) à une date Carbon. */
    private function addWorkingDays(Carbon $date, int $days): Carbon
    {
        $result = $date->copy();
        $added  = 0;
        while ($added < $days) {
            $result->addDay();
            if (! $result->isWeekend()) {
                $added++;
            }
        }
        return $result;
    }
}
