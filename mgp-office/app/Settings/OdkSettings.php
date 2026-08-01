<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class OdkSettings extends Settings
{
    public string $server_type = 'kobo';

    public string $server_url = '';

    /**
     * Token API statique (KoboToolbox, ODK Aggregate).
     * Pour ODK Central : laissé vide - le token de session est généré dynamiquement.
     */
    public string $api_token = '';

    /** Email / nom d'utilisateur (requis pour ODK Central) */
    public string $username = '';

    /** Mot de passe (requis pour ODK Central - POST /v1/sessions) */
    public string $password = '';

    /** Cron expression pour la synchronisation */
    public string $sync_schedule = '*/15 * * * *';

    public bool $sync_enabled = false;

    public int $timeout_seconds = 30;

    public int $retry_attempts = 3;

    public string $webhook_secret = '';

    public static function group(): string
    {
        return 'odk';
    }
}
