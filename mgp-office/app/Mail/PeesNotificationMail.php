<?php

declare(strict_types=1);

namespace App\Mail;

use App\Domains\Vbg\Models\VbgReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Notification PEES à la Banque mondiale pour un incident E&S - délai contractuel 48 h (ER-01).
 *
 * ⚠️  Notification DÉ-IDENTIFIÉE (cf. EsirtNotificationMail). Émise après confirmation du
 *     lien avec le projet par la commission d'enquête (VbgEnquiry).
 */
class PeesNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly VbgReport $report,
        public readonly string $deadline,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[PEES] Notification incident E&S - {$this->report->code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vbg.pees',
            with: [
                'code'           => $this->report->code,
                'classification' => $this->report->classification?->label() ?? 'Non classé',
                'incidentDate'   => $this->report->incident_date?->format('d/m/Y') ?? 'Non précisée',
                'incidentZone'   => $this->report->incident_zone ?? 'Non précisée',
                'deadline'       => $this->deadline,
            ],
        );
    }
}
