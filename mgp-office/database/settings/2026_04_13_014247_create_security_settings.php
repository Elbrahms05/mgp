<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('security.session_lifetime_minutes', 120);
        $this->migrator->add('security.two_factor_required_roles', [
            'super_admin',
            'coordonnateur_ugp',
            'specialiste_vbg',
        ]);
        $this->migrator->add('security.password_min_length', 10);
        $this->migrator->add('security.password_require_uppercase', true);
        $this->migrator->add('security.password_require_number', true);
        $this->migrator->add('security.password_require_symbol', true);
        $this->migrator->add('security.password_expiry_days', 90);
        $this->migrator->add('security.vbg_key_rotation_days', 365);
        $this->migrator->add('security.ip_whitelist', []);
        $this->migrator->add('security.max_login_attempts', 5);
        $this->migrator->add('security.lockout_duration_minutes', 15);
    }
};
