<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Settings\ProjectSettings;
use Illuminate\Database\Seeder;

class ProjectSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = app(ProjectSettings::class);

        $settings->name = 'PACIPA-MGP';
        $settings->code = 'PACIPA';
        $settings->donor = 'Banque mondiale / IDA';
        $settings->project_id = 'P179272';
        $settings->start_date = '2025-01-01';
        $settings->end_date = '2027-12-31';
        $settings->active_regions = [
            'agadez', 'diffa', 'dosso', 'maradi',
            'niamey', 'tahoua', 'tillaberi', 'zinder',
        ];
        $settings->primary_color = '#2e7d32';
        $settings->secondary_color = '#0DB02B';

        $settings->save();
    }
}
