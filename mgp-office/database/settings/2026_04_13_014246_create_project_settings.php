<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('project.name', 'PACIPA-MGP');
        $this->migrator->add('project.code', 'PACIPA');
        $this->migrator->add('project.donor', 'Banque mondiale / IDA');
        $this->migrator->add('project.project_id', 'P179272');
        $this->migrator->add('project.start_date', '2025-01-01');
        $this->migrator->add('project.end_date', '2027-12-31');
        $this->migrator->add('project.active_regions', [
            'agadez', 'diffa', 'dosso', 'maradi',
            'niamey', 'tahoua', 'tillaberi', 'zinder',
        ]);
        $this->migrator->add('project.primary_logo_path', '');
        $this->migrator->add('project.secondary_logo_path', '');
        $this->migrator->add('project.primary_color', '#2e7d32');
        $this->migrator->add('project.secondary_color', '#0DB02B');
        $this->migrator->add('project.contact_email', '');
        $this->migrator->add('project.contact_phone', '');
    }
};
