<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Schéma PostgreSQL séparé pour les données VBG (§4.5)
    |--------------------------------------------------------------------------
    */
    'schema' => env('VBG_SCHEMA', 'vbg'),

    /*
    |--------------------------------------------------------------------------
    | Chiffrement libsodium (XSalsa20-Poly1305)
    | La clé doit être de 32 octets encodés en base64
    |--------------------------------------------------------------------------
    */
    'encryption_key' => env('VBG_ENCRYPTION_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Champs chiffrés par modèle
    |--------------------------------------------------------------------------
    */
    'encrypted_fields' => [
        'vbg_reports' => [
            'survivor_name',
            'survivor_contact',
            'perpetrator_name',
            'incident_description',
            'caller_phone',
        ],
        'vbg_referrals' => [
            'referral_notes',
            'service_provider_contact',
        ],
        'vbg_enquiries' => [
            'findings',
            'recommendations',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rôles autorisés à déchiffrer les données VBG
    |--------------------------------------------------------------------------
    */
    'decrypt_roles' => [
        'super_admin',
        'coordonnateur_ugp',
        'specialiste_vbg',
    ],

    /*
    |--------------------------------------------------------------------------
    | Classification GBVIMS (Annexe 9 du MGP)
    |--------------------------------------------------------------------------
    */
    'gbvims_classifications' => [
        'rape'                   => 'Viol',
        'sexual_assault'         => 'Agression sexuelle',
        'physical_assault'       => 'Agression physique',
        'psychological_emotional'=> 'Violence psychologique/émotionnelle',
        'forced_marriage'        => 'Mariage forcé/précoce',
        'denial_of_resources'    => 'Déni de services, ressources ou opportunités',
        'no_incident'            => 'Aucun incident VBG vérifié',
    ],

    /*
    |--------------------------------------------------------------------------
    | Lien auteur-projet
    |--------------------------------------------------------------------------
    */
    'author_project_links' => [
        'project_worker'    => 'Personnel entreprise / Associé projet',
        'security_forces'   => 'Forces de défense et sécurité (FDS)',
        'unknown'           => 'Inconnu',
        'no_link'           => 'Individu sans lien avec le projet',
    ],

    /*
    |--------------------------------------------------------------------------
    | Types de services de référencement
    |--------------------------------------------------------------------------
    */
    'referral_services' => [
        'medical'       => 'Prise en charge médicale',
        'psychosocial'  => 'Prise en charge psychosociale',
        'legal'         => 'Aide juridique',
        'security'      => 'Sécurité (police/gendarmerie)',
        'other'         => 'Autres services',
    ],

    /*
    |--------------------------------------------------------------------------
    | Centres holistiques VBG (4 centres - §6.4)
    |--------------------------------------------------------------------------
    */
    'holistic_centers' => [
        ['name' => 'Centre holistique de Maradi',   'region' => 'maradi'],
        ['name' => 'Centre holistique de Tahoua',   'region' => 'tahoua'],
        ['name' => 'Centre holistique de Tillabéri','region' => 'tillaberi'],
        ['name' => 'Centre holistique de Zinder',   'region' => 'zinder'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Canaux directs VBG (§4.5)
    |--------------------------------------------------------------------------
    */
    'direct_channels' => [
        'ussd'           => 'USSD (*xxx#)',
        'sms'            => 'SMS (short code)',
        'whatsapp'       => 'WhatsApp Bot',
        'voice_line'     => 'Ligne Verte (appel vocal)',
        'trusted_person' => 'Personne de confiance',
        'point_focal'    => 'Point focal VBG',
        'web'            => 'Formulaire Web',
    ],

    /*
    |--------------------------------------------------------------------------
    | Code VBG format : VBG-REGION-AAAA-NNNN
    |--------------------------------------------------------------------------
    */
    'code_format' => 'VBG-{REGION}-{YEAR}-{SEQ}',

    /*
    |--------------------------------------------------------------------------
    | Purge des métadonnées webhooks VBG (en heures)
    |--------------------------------------------------------------------------
    */
    'webhook_log_retention_hours' => 72,

    /*
    |--------------------------------------------------------------------------
    | Message de réponse SMS/USSD neutre (JAMAIS "VBG" ou "violence")
    |--------------------------------------------------------------------------
    */
    'neutral_response_sms' => 'Merci pour votre message au service PACIPA. Code: {CODE}. Pour toute urgence, composez le {HOTLINE}.',

    'ussd_code'         => env('VBG_USSD_CODE', '*xxx#'),
    'sms_shortcode'     => env('VBG_SMS_SHORTCODE', '3xxx'),
    'hotline_number'    => env('VBG_HOTLINE_NUMBER'),
    'whatsapp_number'   => env('VBG_WHATSAPP_NUMBER'),
];
