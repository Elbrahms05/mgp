<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SmsSettings extends Settings
{
    public string $provider = 'africas_talking';

    public string $api_key = '';

    public string $username = '';

    public string $sender_name = 'PACIPA';

    public string $short_code = '';

    public string $hotline_number = '';

    public array $templates = [];

    public bool $sms_enabled = false;

    public static function group(): string
    {
        return 'sms';
    }
}
