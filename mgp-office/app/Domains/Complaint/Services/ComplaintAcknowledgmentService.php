<?php

declare(strict_types=1);

namespace App\Domains\Complaint\Services;

use App\Domains\Complaint\Models\Complaint;
use App\Models\NotificationLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Génère l'accusé de réception PDF imprimable d'une plainte (EF-06).
 *
 * L'accusé est stocké dans la collection média `acknowledgment` (disque privé) et
 * l'émission est tracée dans `notification_logs`. L'accusé SMS est, lui, émis par
 * ComplaintObserver via ComplaintRegisteredNotification.
 */
class ComplaintAcknowledgmentService
{
    /**
     * Génère et attache le PDF d'accusé. Ne fait jamais échouer l'enregistrement de la
     * plainte : une erreur de génération est journalisée puis tracée en échec.
     */
    public function generate(Complaint $complaint): void
    {
        try {
            $complaint->loadMissing(['complaintType', 'site.locality']);

            $pdf = Pdf::loadView('pdf.complaint-acknowledgment', [
                'complaint'    => $complaint,
                'issuedAt'     => now()->format('d/m/Y H:i'),
                'registeredAt' => $complaint->created_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
                'channel'      => $complaint->channel?->label() ?? 'Non précisé',
                'subject'      => $complaint->subject ?: 'Plainte enregistrée',
                'site'         => $complaint->site?->name,
                'slaDeadline'  => $complaint->sla_deadline?->format('d/m/Y') ?? 'À déterminer',
            ]);

            $complaint->addMediaFromString($pdf->output())
                ->usingFileName("accuse-{$complaint->registration_number}.pdf")
                ->toMediaCollection('acknowledgment');

            $this->log($complaint, NotificationLog::STATUS_SENT);
        } catch (Throwable $e) {
            Log::error('Génération accusé de réception échouée', [
                'complaint'   => $complaint->registration_number,
                'error'       => $e->getMessage(),
            ]);

            $this->log($complaint, NotificationLog::STATUS_FAILED, $e->getMessage());
        }
    }

    private function log(Complaint $complaint, string $status, ?string $error = null): void
    {
        NotificationLog::create([
            'event'           => 'complaint_acknowledgment_pdf',
            'channel'         => 'pdf',
            'recipient'       => $complaint->complainant_phone ?? $complaint->registration_number,
            'subject'         => "Accusé de réception {$complaint->registration_number}",
            'status'          => $status,
            'error_message'   => $error,
            'notifiable_type' => $complaint::class,
            'notifiable_id'   => $complaint->getKey(),
            'sent_at'         => $status === NotificationLog::STATUS_SENT ? now() : null,
        ]);
    }
}
