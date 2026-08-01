<?php

declare(strict_types=1);

namespace App\Notifications\Complaint;

use App\Domains\Complaint\Models\Complaint;
use App\Notifications\AnonymousNotifiable;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Accusé de réception automatique envoyé au plaignant après enregistrement.
 *
 * Canaux : SMS (si numéro renseigné) + database (toujours).
 * ⚠️  Le message NE MENTIONNE JAMAIS "VBG", "violence" ni le type de plainte.
 */
class ComplaintRegisteredNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Complaint $complaint,
    ) {}

    public function via(object $notifiable): array
    {
        // For anonymous notifiables (external complainants), only use SMS
        if ($notifiable instanceof AnonymousNotifiable) {
            return ['sms'];
        }

        $channels = ['database'];

        // SMS uniquement si le plaignant a un numéro et que le SMS est activé
        if (
            ! $this->complaint->is_anonymous
            && $this->complaint->complainant_phone
            && config('services.sms.enabled', false)
        ) {
            $channels[] = 'sms';
        }

        return $channels;
    }

    /**
     * Message SMS - court, neutre, sans mention du type de plainte.
     * Format adapté aux SMS africains (160 caractères max).
     */
    public function toSms(object $notifiable): string
    {
        $ref  = $this->complaint->registration_number;
        $date = $this->complaint->created_at?->format('d/m/Y') ?? now()->format('d/m/Y');

        return "PACIPA: Votre demande #{$ref} a été enregistrée le {$date}. "
             . "Conservez ce numéro pour le suivi. "
             . "Tél: " . config('services.sms.hotline_number', '+22720720001');
    }

    /**
     * Notification stockée en base (panel agent + historique).
     */
    public function toDatabase(object $notifiable): array
    {
        return [
            'type'                => 'complaint_registered',
            'registration_number' => $this->complaint->registration_number,
            'message'             => "Plainte {$this->complaint->registration_number} enregistrée avec succès.",
            'complaint_id'        => $this->complaint->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }
}
