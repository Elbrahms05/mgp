<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Services;

use App\Domains\Committee\Enums\CommitteeLevel;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Models\ComplaintHistory;
use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ComplaintWorkflowService
{
    public function __construct(
        private readonly SlaMonitorService $slaMonitor,
    ) {}

    public function register(array $data, User $registeredBy): Complaint
    {
        $data['status']              = ComplaintStatus::REGISTERED;
        $data['current_level']       = CommitteeLevel::CBGP;
        $data['registered_by']       = $registeredBy->id;
        $data['registration_number'] = $this->generateCode();
        $data['is_anonymous']        = (bool) ($data['is_anonymous'] ?? false);

        $complaint = Complaint::create($data);

        // Calculer l'échéance SLA initiale (niveau CBGP)
        $complaint->sla_deadline = $this->slaMonitor->computeDeadline($complaint);
        $complaint->saveQuietly();

        $this->logHistory($complaint, null, ComplaintStatus::REGISTERED, 'register', 'Plainte enregistrée', $registeredBy);

        return $complaint;
    }

    public function accept(Complaint $complaint, User $by): void
    {
        $from = $complaint->status;
        $complaint->status = ComplaintStatus::ACCEPTED;
        $complaint->save();

        $this->logHistory($complaint, $from, ComplaintStatus::ACCEPTED, 'accept', 'Plainte déclarée recevable', $by);
    }

    public function reject(Complaint $complaint, string $reason, User $by): void
    {
        $from = $complaint->status;
        $complaint->status    = ComplaintStatus::REJECTED;
        $complaint->closed_at = now();
        $complaint->save();

        $this->logHistory($complaint, $from, ComplaintStatus::REJECTED, 'reject', $reason, $by);
    }

    public function startMediation(Complaint $complaint, User $by): void
    {
        $from = $complaint->status;
        $complaint->status = ComplaintStatus::IN_MEDIATION;
        $complaint->save();

        $this->logHistory($complaint, $from, ComplaintStatus::IN_MEDIATION, 'mediation', 'Médiation engagée', $by);
    }

    public function resolve(Complaint $complaint, User $by, ?string $comment = null): void
    {
        $from = $complaint->status;
        $complaint->status = ComplaintStatus::RESOLVED;
        $complaint->save();

        $this->logHistory($complaint, $from, ComplaintStatus::RESOLVED, 'resolve', $comment ?? 'Plainte résolue', $by);
    }

    public function close(Complaint $complaint, User $by, ?string $comment = null): void
    {
        $from = $complaint->status;
        $complaint->status    = ComplaintStatus::CLOSED;
        $complaint->closed_at = now();
        $complaint->save();

        $this->logHistory($complaint, $from, ComplaintStatus::CLOSED, 'close', $comment ?? 'Plainte clôturée', $by);
    }

    public function escalate(Complaint $complaint, CommitteeLevel $to, string $comment, User $by): void
    {
        $from = $complaint->status;
        $complaint->status        = ComplaintStatus::ESCALATED;
        $complaint->current_level = $to;
        $complaint->sla_deadline  = $this->slaMonitor->computeDeadline($complaint, $to);
        $complaint->save();

        $this->logHistory(
            $complaint,
            $from,
            ComplaintStatus::ESCALATED,
            'escalate',
            "Escaladée vers {$to->label()} - {$comment}",
            $by,
        );
    }

    public function generateCode(): string
    {
        $year    = now()->year;
        $last    = Complaint::whereYear('created_at', $year)->count() + 1;
        $seq     = str_pad((string) $last, 6, '0', STR_PAD_LEFT);
        return "REG-{$year}-{$seq}";
    }

    private function logHistory(
        Complaint $complaint,
        ?ComplaintStatus $from,
        ComplaintStatus $to,
        string $action,
        ?string $comment,
        User $by,
    ): void {
        ComplaintHistory::create([
            'complaint_id' => $complaint->id,
            'from_status'  => $from?->value,
            'to_status'    => $to->value,
            'action'       => $action,
            'comment'      => $comment,
            'performed_by' => $by->id,
        ]);
    }
}
