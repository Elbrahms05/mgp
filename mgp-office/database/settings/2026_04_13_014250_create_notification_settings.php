<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('notifications.channels', [
            'complaint_registered'   => ['sms', 'database'],
            'complaint_escalated'    => ['sms', 'email', 'database'],
            'complaint_resolved'     => ['sms', 'database'],
            'sla_breach'             => ['email', 'database'],
            'sla_reminder'           => ['database'],
            'vbg_received_by_ugp'    => ['email', 'database'],
            'vbg_referral_confirmed' => ['database'],
            'worldbank_esirt'        => ['email'],
            'worldbank_pees'         => ['email'],
        ]);
        $this->migrator->add('notifications.recipients', [
            'sla_breach'          => ['roles' => ['coordonnateur_ugp', 'chef_urc']],
            'vbg_received_by_ugp' => ['roles' => ['specialiste_vbg', 'coordonnateur_ugp']],
            'worldbank_esirt'     => ['emails' => []],
            'worldbank_pees'      => ['emails' => []],
        ]);
        $this->migrator->add('notifications.sla_reminder_frequency_minutes', 60);
        $this->migrator->add('notifications.email_enabled', true);
        $this->migrator->add('notifications.sms_enabled', false);
    }
};
