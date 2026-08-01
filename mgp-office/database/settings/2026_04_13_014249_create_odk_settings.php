<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('odk.server_type', 'kobo');
        $this->migrator->add('odk.server_url', '');
        $this->migrator->add('odk.api_token', '');
        $this->migrator->add('odk.username', '');
        $this->migrator->add('odk.sync_schedule', '*/15 * * * *');
        $this->migrator->add('odk.sync_enabled', false);
        $this->migrator->add('odk.timeout_seconds', 30);
        $this->migrator->add('odk.retry_attempts', 3);
        $this->migrator->add('odk.webhook_secret', '');
    }
};
