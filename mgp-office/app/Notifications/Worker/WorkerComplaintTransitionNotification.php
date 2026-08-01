<?php

declare(strict_types=1);

namespace App\Notifications\Worker;

use App\Domains\Worker\Models\WorkerComplaint;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Notification envoyée aux gestionnaires MGP lors d'une transition de statut
 * d'une plainte travailleur.
 *
 * Canal : database uniquement (pas de SMS - la plainte n'est pas anonyme).
 */
class WorkerComplaintTransitionNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly WorkerComplaint $complaint,
        private readonly string          $event,
        private readonly User            $by,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        $label = match ($this->event) {
            'start_review'  => "Examen démarré",
            'escalate'      => "Escaladée N2",
            'auto_escalate' => "Auto-escaladée (SLA dépassé)",
            'resolve'       => "Résolue",
            default         => ucfirst($this->event),
        };

        return [
            'type'                => 'worker_complaint_transition',
            'registration_number' => $this->complaint->registration_number,
            'event'               => $this->event,
            'message'             => "Plainte travailleur {$this->complaint->registration_number} - {$label} par {$this->by->name}.",
            'worker_complaint_id' => $this->complaint->id,
            'worker_name'         => $this->complaint->worker_name,
            'circuit'             => $this->complaint->circuitLabel(),
            'sla_deadline'        => $this->complaint->sla_deadline?->toDateString(),
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
