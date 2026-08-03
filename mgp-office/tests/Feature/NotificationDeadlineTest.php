<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domains\Vbg\Models\VbgReport;
use App\Domains\Vbg\Services\WorldBankNotificationService;
use App\Mail\EsirtNotificationMail;
use App\Settings\NotificationSettings;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * ER-01 - La notification ESIRT (EAS/HS) est réellement émise vers les points
 * focaux Banque mondiale configurés, tracée dans notification_logs, et marque
 * le signalement comme notifié (délai contractuel 24 h).
 */
class NotificationDeadlineTest extends TestCase
{
    use RefreshDatabase;

    public function test_notify_esirt_envoie_le_mail_et_trace_le_log(): void
    {
        Mail::fake();
        Carbon::setTestNow('2026-01-01 08:00:00');

        // Configure un point focal Banque mondiale
        $settings = app(NotificationSettings::class);
        $recipients = $settings->recipients;
        $recipients['worldbank_esirt'] = ['emails' => ['esirt@banquemondiale.org']];
        $settings->recipients = $recipients;
        $settings->save();

        $report = VbgReport::create([
            'code'           => 'VBG-2026-00001',
            'channel'        => 'ussd',
            'is_anonymous'   => true,
            'classification' => 'rape',
            'incident_zone'  => 'Maradi',
            'incident_date'  => '2025-12-30',
            'status'         => 'new',
        ]);

        app(WorldBankNotificationService::class)->notifyEsirt($report);

        Mail::assertSent(EsirtNotificationMail::class, function (EsirtNotificationMail $mail) {
            return $mail->hasTo('esirt@banquemondiale.org');
        });

        $this->assertDatabaseHas('notification_logs', [
            'event'     => 'worldbank_esirt',
            'channel'   => 'email',
            'recipient' => 'esirt@banquemondiale.org',
            'status'    => 'sent',
        ]);

        $this->assertNotNull($report->refresh()->worldbank_notified_at);

        Carbon::setTestNow();
    }

    public function test_sans_destinataire_configure_le_log_reste_en_attente(): void
    {
        Mail::fake();

        $settings = app(NotificationSettings::class);
        $recipients = $settings->recipients;
        $recipients['worldbank_esirt'] = ['emails' => []];
        $settings->recipients = $recipients;
        $settings->save();

        $report = VbgReport::create([
            'code'         => 'VBG-2026-00002',
            'channel'      => 'sms',
            'is_anonymous' => true,
            'status'       => 'new',
        ]);

        app(WorldBankNotificationService::class)->notifyEsirt($report);

        Mail::assertNothingSent();
        $this->assertDatabaseHas('notification_logs', [
            'event'  => 'worldbank_esirt',
            'status' => 'pending',
        ]);
    }
}
