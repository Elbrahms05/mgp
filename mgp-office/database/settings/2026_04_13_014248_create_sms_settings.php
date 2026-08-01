<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('sms.provider', 'africas_talking');
        $this->migrator->add('sms.api_key', '');
        $this->migrator->add('sms.username', '');
        $this->migrator->add('sms.sender_name', 'PACIPA');
        $this->migrator->add('sms.short_code', '');
        $this->migrator->add('sms.hotline_number', '');
        $this->migrator->add('sms.templates', []);
        $this->migrator->add('sms.sms_enabled', false);
    }
};
