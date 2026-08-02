<?php

declare(strict_types=1);

namespace App\Observers\Complaint;

use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Domains\Complaint\Services\ComplaintAcknowledgmentService;
use App\Notifications\Complaint\ComplaintRegisteredNotification;

/**
 * Observer Complaint - déclenche l'accusé de réception et les notifications automatiques.
 *
 * L'accusé (EF-06) est ainsi produit pour TOUTES les voies de création d'une plainte :
 * saisie back-office, import ODK (SubmissionMapper::mapF01) et, à terme, webhook SMS.
 */
class ComplaintObserver
{
    public function __construct(
        private readonly ComplaintAcknowledgmentService $acknowledgment,
    ) {}

    /**
     * Accusé de réception (PDF + SMS + notification tableau de bord) dès la création.
     */
    public function created(Complaint $complaint): void
    {
        // Accusé de réception SMS au plaignant (via un notifiable anonyme portant son téléphone)
        if (! $complaint->is_anonymous && $complaint->complainant_phone) {
            $notifiable = new \App\Notifications\AnonymousNotifiable($complaint->complainant_phone);
            $notifiable->notify(new ComplaintRegisteredNotification($complaint));
        }

        // Notification « tableau de bord » (canal database) : destinée à l'agent enregistreur.
        // Le canal database ne supporte pas les notifications on-demand : il faut un notifiable
        // Eloquent réel.
        $complaint->registeredBy?->notify(new ComplaintRegisteredNotification($complaint));

        // Accusé de réception PDF imprimable (EF-06)
        $this->acknowledgment->generate($complaint);
    }

    /**
     * Lors d'une escalade, réinitialise le deadline SLA dans la notification.
     */
    public function updated(Complaint $complaint): void
    {
        if (
            $complaint->wasChanged('current_level')
            && $complaint->status === ComplaintStatus::ESCALATED
        ) {
            // Notification déjà gérée par ComplaintWorkflowService::escalate → logHistory
        }
    }
}
