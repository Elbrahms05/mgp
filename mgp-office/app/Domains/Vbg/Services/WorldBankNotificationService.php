<?php

declare(strict_types=1);

namespace App\Domains\Vbg\Services;

use App\Domains\Vbg\Models\VbgReport;
use App\Settings\SlaSettings;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class WorldBankNotificationService
{
    public function __construct(
        private readonly SlaSettings $settings,
    ) {}

    /**
     * Notification UGP → Banque mondiale : ESIRT (EAS/HS) - délai 24h contractuel.
     */
    public function notifyEsirt(VbgReport $report): void
    {
        $deadline = now()->addHours($this->settings->vbg_worldbank_eas_hours);

        Log::channel('vbg')->info('WorldBank ESIRT notification triggered', [
            'report_code' => $report->code,
            'deadline'    => $deadline->toDateTimeString(),
        ]);

        // TODO (phase 2) : envoi email sécurisé ESIRT
        // Mail::to(config('vbg.esirt_email'))->send(new EsirtNotificationMail($report));

        $report->worldbank_notified_at = now();
        $report->saveQuietly();
    }

    /**
     * Notification UGP → Banque mondiale : PEES (incidents E&S) - délai 48h contractuel.
     */
    public function notifyPees(VbgReport $report): void
    {
        $deadline = now()->addHours($this->settings->vbg_worldbank_incident_hours);

        Log::channel('vbg')->info('WorldBank PEES notification triggered', [
            'report_code' => $report->code,
            'deadline'    => $deadline->toDateTimeString(),
        ]);

        // TODO (phase 2) : envoi email sécurisé PEES
        // Mail::to(config('vbg.pees_email'))->send(new PeesNotificationMail($report));

        $report->worldbank_notified_at = now();
        $report->saveQuietly();
    }
}
