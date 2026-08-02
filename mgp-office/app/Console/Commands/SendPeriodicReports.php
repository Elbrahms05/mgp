<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Domains\Statistic\Services\ReportPdfService;
use App\Mail\PeriodicReportMail;
use App\Models\NotificationLog;
use App\Models\User;
use App\Settings\NotificationSettings;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Envoi automatique périodique des rapports aux personnes habilitées (EF-05/EF-14/ER-04).
 *
 * Périodes : monthly → tableau de bord du mois écoulé ; quarterly → VBG agrégé du trimestre
 * écoulé ; annual → rapport annuel de l'année écoulée. Destinataires depuis NotificationSettings.
 */
class SendPeriodicReports extends Command
{
    protected $signature = 'reports:send-periodic {--period=monthly : monthly|quarterly|annual}';

    protected $description = 'Génère et envoie les rapports périodiques aux personnes habilitées';

    /** period => [report_type, title, [from, to]] */
    private function resolvePeriod(string $period): array
    {
        return match ($period) {
            'quarterly' => [
                'quarterly_vbg',
                'Rapport VBG agrégé trimestriel',
                now()->subQuarter()->startOfQuarter(),
                now()->subQuarter()->endOfQuarter(),
            ],
            'annual' => [
                'annual_mgp',
                'Rapport annuel MGP',
                now()->subYear()->startOfYear(),
                now()->subYear()->endOfYear(),
            ],
            default => [
                'monthly_dashboard',
                'Tableau de bord mensuel',
                now()->subMonthNoOverflow()->startOfMonth(),
                now()->subMonthNoOverflow()->endOfMonth(),
            ],
        };
    }

    public function handle(NotificationSettings $settings, ReportPdfService $reports): int
    {
        $period = (string) $this->option('period');

        [$reportType, $title, $from, $to] = $this->resolvePeriod($period);

        $config = $settings->report_recipients[$reportType] ?? null;

        if (! is_array($config) || ($config['enabled'] ?? false) !== true) {
            $this->info("Envoi désactivé pour {$reportType}.");
            return self::SUCCESS;
        }

        $recipients = $this->recipients($config);

        if ($recipients === []) {
            $this->warn("Aucun destinataire configuré pour {$reportType}.");
            $this->log($reportType, 'config', NotificationLog::STATUS_PENDING, 'Aucun destinataire configuré.');
            return self::SUCCESS;
        }

        $pdf      = $reports->forType($reportType, $from, $to);
        $content  = $pdf->output();
        $fileName = "rapport_{$reportType}_{$from->format('Ymd')}_{$to->format('Ymd')}.pdf";
        $periodLabel = "{$from->format('d/m/Y')} → {$to->format('d/m/Y')}";

        foreach ($recipients as $email) {
            try {
                Mail::to($email)->send(new PeriodicReportMail($title, $periodLabel, $content, $fileName));
                $this->log($reportType, $email, NotificationLog::STATUS_SENT);
                $this->info("Rapport {$reportType} envoyé à {$email}.");
            } catch (Throwable $e) {
                $this->log($reportType, $email, NotificationLog::STATUS_FAILED, $e->getMessage());
                $this->error("Échec envoi à {$email} : {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array{roles?: list<string>, emails?: list<string>}  $config
     * @return list<string>
     */
    private function recipients(array $config): array
    {
        $emails = collect($config['emails'] ?? []);

        if (! empty($config['roles'])) {
            $roleEmails = User::whereHas('roles', fn ($q) => $q->whereIn('name', $config['roles']))
                ->pluck('email');
            $emails = $emails->merge($roleEmails);
        }

        return $emails
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL) !== false)
            ->unique()
            ->values()
            ->all();
    }

    private function log(string $reportType, string $recipient, string $status, ?string $error = null): void
    {
        NotificationLog::create([
            'event'           => "report_{$reportType}",
            'channel'         => 'email',
            'recipient'       => $recipient,
            'subject'         => "Rapport périodique {$reportType}",
            'status'          => $status,
            'error_message'   => $error,
            'notifiable_type' => self::class,
            'notifiable_id'   => 0,
            'sent_at'         => $status === NotificationLog::STATUS_SENT ? now() : null,
        ]);
    }
}
