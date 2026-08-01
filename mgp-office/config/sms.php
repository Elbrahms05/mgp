<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Provider SMS (Orange Niger, Airtel, Moov, Africa's Talking...)
    |--------------------------------------------------------------------------
    */
    'provider' => env('SMS_PROVIDER', 'africas_talking'),

    /*
    |--------------------------------------------------------------------------
    | Credentials par provider
    |--------------------------------------------------------------------------
    */
    'providers' => [

        'africas_talking' => [
            'username' => env('AT_USERNAME'),
            'api_key'  => env('AT_API_KEY'),
            'from'     => env('AT_SENDER_ID', 'PACIPA'),
        ],

        'orange_niger' => [
            'client_id'     => env('ORANGE_CLIENT_ID'),
            'client_secret' => env('ORANGE_CLIENT_SECRET'),
            'sender_name'   => env('ORANGE_SENDER_NAME', 'PACIPA-MGP'),
        ],

        'twilio' => [
            'account_sid' => env('TWILIO_ACCOUNT_SID'),
            'auth_token'  => env('TWILIO_AUTH_TOKEN'),
            'from'        => env('TWILIO_FROM'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Numéro expéditeur / Short code
    |--------------------------------------------------------------------------
    */
    'short_code'  => env('SMS_SHORTCODE', '3xxx'),
    'sender_name' => env('SMS_SENDER_NAME', 'PACIPA'),

    /*
    |--------------------------------------------------------------------------
    | Templates SMS (variables : {NOM}, {CODE}, {DATE}, {DELAI}, {LIEN})
    |--------------------------------------------------------------------------
    */
    'templates' => [

        'complaint_receipt' =>
            'PACIPA-MGP: Votre plainte {CODE} a été enregistrée le {DATE}. ' .
            'Vous serez informé(e) de la suite dans les {DELAI} jours. Merci.',

        'complaint_resolved' =>
            'PACIPA-MGP: Votre plainte {CODE} a été traitée. ' .
            'Pour toute question, contactez votre comité local.',

        'complaint_escalated' =>
            'PACIPA-MGP: Votre plainte {CODE} a été transmise au niveau supérieur. ' .
            'Un agent vous contactera prochainement.',

        'sla_reminder_complainant' =>
            'PACIPA-MGP: Votre plainte {CODE} est en cours de traitement. ' .
            'Délai de réponse prévu : {DATE}.',

        'vbg_neutral_receipt' =>
            'Merci pour votre message au service PACIPA. ' .
            'Code: {CODE}. En cas d\'urgence appelez le {HOTLINE}.',

        'vbg_followup_callback' =>
            'Un(e) spécialiste PACIPA vous contactera dans les 24h au numéro que vous avez indiqué.',

        'committee_notification' =>
            'PACIPA-MGP: Une nouvelle plainte {CODE} a été enregistrée dans votre zone. ' .
            'Veuillez consulter le système.',

        'sla_breach_alert' =>
            'PACIPA-MGP ALERTE: La plainte {CODE} dépasse son délai SLA. ' .
            'Action requise immédiatement.',
    ],
];
