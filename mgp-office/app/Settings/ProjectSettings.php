<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ProjectSettings extends Settings
{
    public string $name = 'PACIPA-MGP';

    public string $code = 'PACIPA';

    public string $donor = 'Banque mondiale / IDA';

    public string $project_id = 'P179272';

    public string $start_date = '2025-01-01';

    public string $end_date = '2027-12-31';

    public array $active_regions = [
        'agadez', 'diffa', 'dosso', 'maradi',
        'niamey', 'tahoua', 'tillaberi', 'zinder',
    ];

    public string $primary_logo_path = '';

    public string $secondary_logo_path = '';

    public string $primary_color = '#2e7d32';

    public string $secondary_color = '#0DB02B';

    public string $contact_email = '';

    public string $contact_phone = '';

    public static function group(): string
    {
        return 'project';
    }
}
