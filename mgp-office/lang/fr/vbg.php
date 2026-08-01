<?php

/*
 * Terminologie VBG/EAS/HS - PACIPA MGP
 * Référence : Annexe 9 du MGP et classification GBVIMS ONU
 */

return [
    'title'       => 'Plaintes VBG / EAS / HS',
    'restricted'  => 'Accès restreint',
    'confidential'=> 'CONFIDENTIEL - Données chiffrées',

    'consent' => [
        'title'    => 'Consentement éclairé',
        'required' => 'Le consentement éclairé de la survivante est obligatoire avant tout enregistrement.',
        'given'    => 'Consentement donné',
        'refused'  => 'Consentement refusé - enregistrement anonymisé uniquement',
        'warning'  => '⚠️ En cas de refus de consentement, seules les données anonymisées minimales sont enregistrées.',
    ],

    'gbvims' => [
        'rape'              => 'Viol',
        'sexual_assault'    => 'Agression sexuelle (autre que viol)',
        'physical_assault'  => 'Agression physique',
        'psychological'     => 'Violence psychologique / émotionnelle',
        'forced_marriage'   => 'Mariage forcé / précoce',
        'denial_resources'  => 'Déni de services, ressources ou opportunités',
        'none_verified'     => 'Aucun incident VBG vérifié',
    ],

    'author_link' => [
        'project_staff'    => 'Personnel entreprise / associé projet',
        'security_forces'  => 'Forces de sécurité (FDS)',
        'unknown'          => 'Inconnu',
        'unrelated'        => 'Individu sans lien avec le projet',
    ],

    'referral_services' => [
        'medical'       => 'Prise en charge médicale',
        'psychosocial'  => 'Prise en charge psychosociale',
        'legal'         => 'Aide juridique',
        'security'      => 'Sécurité (police / gendarmerie)',
        'other'         => 'Autres services',
    ],

    'channels' => [
        'ussd'           => 'USSD (*xxx#)',
        'sms'            => 'SMS (short code)',
        'whatsapp'       => 'WhatsApp Bot',
        'voice_line'     => 'Ligne verte (appel vocal)',
        'trusted_person' => 'Personne de confiance',
        'point_focal'    => 'Point focal VBG',
        'web'            => 'Saisie web back-office',
    ],

    'notifications' => [
        'ugp_24h'        => 'Notification UGP requise sous 24h',
        'referral_72h'   => 'Référencement services requis sous 72h',
        'worldbank_24h'  => 'Notification Banque mondiale requise sous 24h (EAS/HS)',
        'worldbank_48h'  => 'Notification Banque mondiale requise sous 48h (incident E&S)',
        'enquiry_30d'    => 'Commission d\'enquête : 30 jours maximum',
    ],

    'warnings' => [
        'no_mediation'   => '⛔ INTERDIT : Aucune médiation ni conciliation pour les plaintes EAS/HS.',
        'no_guilt'       => '⚠️ La commission vérifie uniquement le lien avec le projet - JAMAIS la culpabilité.',
        'encrypted'      => '🔒 Ces données sont chiffrées. Seuls les rôles autorisés peuvent les déchiffrer.',
        'no_log'         => 'Ces données ne doivent JAMAIS apparaître dans les journaux système.',
    ],

    'fields' => [
        'code'             => 'Code plainte VBG',
        'survivor_age_sex' => 'Âge et sexe de la survivante',
        'incident_date'    => 'Date de l\'incident',
        'incident_location'=> 'Lieu de l\'incident',
        'author_link'      => 'Lien auteur présumé avec le projet',
        'classification'   => 'Classification GBVIMS',
        'services_received'=> 'Services reçus',
        'consent'          => 'Consentement',
        'is_anonymous'     => 'Signalement anonyme',
        'channel'          => 'Canal de signalement',
    ],

    'messages' => [
        'received'        => 'Signalement VBG enregistré. Notification UGP déclenchée (24h).',
        'referred'        => 'Survivante référencée vers les services de prise en charge.',
        'enquiry_created' => 'Commission d\'enquête constituée.',
        'worldbank_sent'  => 'Notification envoyée à la Banque mondiale.',
    ],
];
