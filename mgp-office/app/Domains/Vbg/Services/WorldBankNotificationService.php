<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Models\VbgReport;
use App\Mail\EsirtNotificationMail;
use App\Mail\PeesNotificationMail;
use App\Models\NotificationLog;
use App\Settings\NotificationSettings;
use App\Settings\SlaSettings;
use Carbon\Carbon;
use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Émet les notifications contractuelles UGP → Banque mondiale (ER-01).
 *
 * ESIRT (EAS/HS) : 24 h · PEES (incidents E&S) : 48 h.
 * Chaque envoi est réellement effectué (Mailable dé-identifié) puis tracé dans
 * `notification_logs` comme preuve de respect du délai. Les destinataires (« points
 * focaux Banque mondiale ») proviennent de NotificationSettings.
 */
class WorldBankNotificationService
{
    public function __construct(
        private readonly SlaSettings $slaSettings,
        private readonly NotificationSettings $notificationSettings,
    ) {}

    /**
     * Notification UGP → Banque mondiale : ESIRT (EAS/HS) - délai 24 h contractuel.
     */
    public function notifyEsirt(VbgReport $report): void
    {
        $deadline = now()->addHours($this->slaSettings->vbg_worldbank_eas_hours);

        $this->dispatch(
            report: $report,
            event: 'worldbank_esirt',
            deadline: $deadline,
            mailFactory: fn (string $recipient) => new EsirtNotificationMail($report, $deadline->toDateTimeString()),
        );

        $report->worldbank_notified_at = now();
        $report->saveQuietly();
    }

    /**
     * Notification UGP → Banque mondiale : PEES (incidents E&S) - délai 48 h contractuel.
     */
    public function notifyPees(VbgReport $report): void
    {
        $deadline = now()->addHours($this->slaSettings->vbg_worldbank_incident_hours);

        $this->dispatch(
            report: $report,
            event: 'worldbank_pees',
            deadline: $deadline,
            mailFactory: fn (string $recipient) => new PeesNotificationMail($report, $deadline->toDateTimeString()),
        );

        $report->worldbank_notified_at = now();
        $report->saveQuietly();
    }

    /**
     * Résout les destinataires configurés et envoie le Mailable à chacun, avec traçage.
     *
     * @param  callable(string):Mailable  $mailFactory
     */
    private function dispatch(VbgReport $report, string $event, Carbon $deadline, callable $mailFactory): void
    {
        $recipients = $this->recipientsFor($event);

        Log::channel('vbg')->info('WorldBank notification triggered', [
            'event'       => $event,
            'report_code' => $report->code,
            'deadline'    => $deadline->toDateTimeString(),
            'recipients'  => count($recipients),
        ]);

        if ($recipients === []) {
            // Aucun point focal configuré : on trace tout de même l'échéance pour l'audit.
            $this->log($report, $event, 'config', null, NotificationLog::STATUS_PENDING,
                'Aucun destinataire Banque mondiale configuré dans les Paramètres.');

            return;
        }

        foreach ($recipients as $recipient) {
            /** @var Mailable $mail */
            $mail = $mailFactory($recipient);

            try {
                Mail::to($recipient)->send($mail);

                $this->log($report, $event, $recipient, $mail->subject ?? null, NotificationLog::STATUS_SENT);
            } catch (Throwable $e) {
                Log::channel('vbg')->error('WorldBank notification failed', [
                    'event'       => $event,
                    'report_code' => $report->code,
                    'error'       => $e->getMessage(),
                ]);

                $this->log($report, $event, $recipient, $mail->subject ?? null, NotificationLog::STATUS_FAILED, $e->getMessage());
            }
        }
    }

    /**
     * Adresses des points focaux Banque mondiale pour l'événement donné.
     *
     * @return list<string>
     */
    private function recipientsFor(string $event): array
    {
        $emails = $this->notificationSettings->recipients[$event]['emails'] ?? [];

        return array_values(array_filter(
            array_map('trim', (array) $emails),
            static fn (string $email): bool => filter_var($email, FILTER_VALIDATE_EMAIL) !== false,
        ));
    }

    private function log(
        VbgReport $report,
        string $event,
        string $recipient,
        ?string $subject,
        string $status,
        ?string $error = null,
    ): void {
        // Le corps n'est jamais journalisé : la notification est dé-identifiée mais on
        // évite par principe de dupliquer tout contenu VBG dans les logs (règle n°1).
        NotificationLog::create([
            'event'           => $event,
            'channel'         => 'email',
            'recipient'       => $recipient,
            'subject'         => $subject,
            'status'          => $status,
            'error_message'   => $error,
            'notifiable_type' => $report::class,
            'notifiable_id'   => $report->getKey(),
            'sent_at'         => $status === NotificationLog::STATUS_SENT ? now() : null,
        ]);
    }
}
