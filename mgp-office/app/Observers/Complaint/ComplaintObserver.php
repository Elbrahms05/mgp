<?php

declare(strict_types=1);

namespace App\Observers\Complaint;

use App\Domains\Complaint\Enums\ComplaintStatus;
use App\Domains\Complaint\Models\Complaint;
use App\Notifications\Complaint\ComplaintRegisteredNotification;
use Illuminate\Support\Facades\Notification;

/**
 * Observer Complaint - déclenche les notifications automatiques sur les transitions d'état.
 */
class ComplaintObserver
{
    /**
     * Accusé de réception SMS/database dès qu'une plainte est créée.
     * Le plaignant est notifié via son numéro de téléphone si disponible.
     */
    public function created(Complaint $complaint): void
    {
        // Notifier le plaignant (via un notifiable anonyme portant son téléphone)
        if (! $complaint->is_anonymous && $complaint->complainant_phone) {
            $notifiable = new \App\Notifications\AnonymousNotifiable($complaint->complainant_phone);
            $notifiable->notify(new ComplaintRegisteredNotification($complaint));
        }

        // Toujours enregistrer en base (pour le tableau de bord agent)
        Notification::route('database', null)
            ->notify(new ComplaintRegisteredNotification($complaint));
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
