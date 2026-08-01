<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Type de serveur ODK (kobo | odk_central | odk_aggregate)
    |--------------------------------------------------------------------------
    */
    'server_type' => env('ODK_SERVER_TYPE', 'kobo'),

    /*
    |--------------------------------------------------------------------------
    | URL du serveur central ODK/KoBoToolbox
    |--------------------------------------------------------------------------
    */
    'server_url' => env('ODK_SERVER_URL'),

    /*
    |--------------------------------------------------------------------------
    | Credentials API
    |--------------------------------------------------------------------------
    */
    'api_token'  => env('ODK_API_TOKEN'),
    'username'   => env('ODK_USERNAME'),
    'password'   => env('ODK_PASSWORD'),

    /*
    |--------------------------------------------------------------------------
    | Configuration KoBoToolbox (si server_type = kobo)
    |--------------------------------------------------------------------------
    */
    'kobo' => [
        'asset_uid'    => env('KOBO_ASSET_UID'),
        'api_version'  => env('KOBO_API_VERSION', 'v2'),
        'base_url'     => env('KOBO_BASE_URL', 'https://kf.kobotoolbox.org'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration ODK Central (si server_type = odk_central)
    |--------------------------------------------------------------------------
    */
    'central' => [
        'project_id' => env('ODK_CENTRAL_PROJECT_ID'),
        'email'      => env('ODK_CENTRAL_EMAIL'),
        'password'   => env('ODK_CENTRAL_PASSWORD'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Fréquence de synchronisation (cron expression)
    |--------------------------------------------------------------------------
    */
    'sync_schedule' => env('ODK_SYNC_SCHEDULE', '*/15 * * * *'),

    /*
    |--------------------------------------------------------------------------
    | Mapping formulaires ODK → modèles Eloquent
    |--------------------------------------------------------------------------
    */
    'form_mappings' => [
        'pacipa_f01' => \App\Domains\Complaint\Models\Complaint::class,
        'pacipa_f02' => \App\Domains\Complaint\Models\Complaint::class,
        'pacipa_f03' => \App\Domains\Vbg\Models\VbgReport::class,
        'pacipa_f04' => \App\Domains\Mediation\Models\Mediation::class,
        'pacipa_f06' => \App\Domains\Committee\Models\Committee::class,
        'pacipa_f07' => \App\Domains\Evaluation\Models\CommitteeEvaluation::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeout des appels API (secondes)
    |--------------------------------------------------------------------------
    */
    'timeout' => env('ODK_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Nombre de tentatives en cas d'échec
    |--------------------------------------------------------------------------
    */
    'retry_attempts' => env('ODK_RETRY_ATTEMPTS', 3),
    'retry_delay_ms' => env('ODK_RETRY_DELAY_MS', 1000),

    /*
    |--------------------------------------------------------------------------
    | Webhook secret pour vérification des payloads entrants
    |--------------------------------------------------------------------------
    */
    'webhook_secret' => env('ODK_WEBHOOK_SECRET'),
];
