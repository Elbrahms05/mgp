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
 * Notification ESIRT à la Banque mondiale pour un cas EAS/HS - délai contractuel 24 h (ER-01).
 *
 * ⚠️  Notification DÉ-IDENTIFIÉE : ne contient QUE le code VBG, la classification agrégée
 *     (GBVIMS), la date et la zone de l'incident, jamais l'identité de la survivante ni
 *     l'auteur présumé (règle critique n°1 : seul le code VBG circule hors du module).
 */
class EsirtNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly VbgReport $report,
        public readonly string $deadline,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[ESIRT] Notification EAS/HS - {$this->report->code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.vbg.esirt',
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
