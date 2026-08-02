<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Envoi automatique périodique d'un rapport aux personnes habilitées (EF-05/EF-14/ER-04).
 */
class PeriodicReportMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $reportTitle,
        public readonly string $period,
        public readonly string $pdfContent,
        public readonly string $pdfFileName,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: "[PACIPA-MGP] {$this->reportTitle} — {$this->period}");
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.reports.periodic',
            with: [
                'reportTitle' => $this->reportTitle,
                'period'      => $this->period,
            ],
        );
    }

    public function attachments(): array
    {
        return [
            \Illuminate\Mail\Mailables\Attachment::fromData(fn () => $this->pdfContent, $this->pdfFileName)
                ->withMime('application/pdf'),
        ];
    }
}
