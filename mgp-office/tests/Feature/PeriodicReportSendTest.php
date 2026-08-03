<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Mail\PeriodicReportMail;
use App\Models\User;
use App\Settings\NotificationSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

/**
 * EF-05 / EF-14 / ER-04 - L'envoi automatique périodique adresse le rapport aux personnes
 * habilitées (par rôle/email des Paramètres) et trace l'émission.
 */
class PeriodicReportSendTest extends TestCase
{
    use RefreshDatabase;

    private function coordinator(string $email): User
    {
        Role::findOr('coordonnateur_ugp', fn () => Role::create(['name' => 'coordonnateur_ugp']));
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $user = User::factory()->create(['email' => $email]);
        $user->assignRole('coordonnateur_ugp');

        return $user;
    }

    public function test_le_rapport_mensuel_est_envoye_aux_destinataires_configures(): void
    {
        Mail::fake();
        $this->coordinator('coordo@pacipa.ne');

        $this->artisan('reports:send-periodic --period=monthly')->assertSuccessful();

        Mail::assertSent(PeriodicReportMail::class, fn (PeriodicReportMail $mail) => $mail->hasTo('coordo@pacipa.ne'));

        $this->assertDatabaseHas('notification_logs', [
            'event'     => 'report_monthly_dashboard',
            'recipient' => 'coordo@pacipa.ne',
            'status'    => 'sent',
        ]);
    }

    public function test_l_envoi_desactive_n_envoie_rien(): void
    {
        Mail::fake();
        $this->coordinator('coordo@pacipa.ne');

        // Désactive l'envoi du tableau de bord mensuel
        $settings = app(NotificationSettings::class);
        $recipients = $settings->report_recipients;
        $recipients['monthly_dashboard']['enabled'] = false;
        $settings->report_recipients = $recipients;
        $settings->save();

        $this->artisan('reports:send-periodic --period=monthly')->assertSuccessful();

        Mail::assertNothingSent();
    }
}
