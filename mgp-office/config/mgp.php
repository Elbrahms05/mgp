<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Types de plaintes (Tableau 4 du MGP)
    |--------------------------------------------------------------------------
    */
    'complaint_types' => [
        1 => 'Inaccessibilité aux informations liées au Projet',
        2 => 'Plaintes liées à l\'exécution des marchés',
        3 => 'Plaintes liées aux travaux de réhabilitation',
        4 => 'Plaintes liées aux conditions de travail',
        5 => 'Plaintes liées aux pertes/affectation biens physiques',
        6 => 'Plaintes liées à l\'octroi d\'équipements',
        7 => 'Plaintes liées aux formations',
        8 => 'Plaintes VBG/EAS/HS et VCE',
    ],

    /*
    |--------------------------------------------------------------------------
    | Types sensibles (circuit VBG - jamais de médiation)
    |--------------------------------------------------------------------------
    */
    'sensitive_complaint_types' => [8],

    /*
    |--------------------------------------------------------------------------
    | Canaux de réception des plaintes (§4.3.2 du MGP)
    |--------------------------------------------------------------------------
    */
    'complaint_channels' => [
        'in_person'    => 'En personne',
        'phone'        => 'Téléphone',
        'sms'          => 'SMS',
        'whatsapp'     => 'WhatsApp',
        'letter'       => 'Courrier',
        'email'        => 'Email',
        'complaint_box'=> 'Boîte de réclamation',
        'hotline'      => 'Ligne verte',
        'web'          => 'Boîte réclamation Web',
        'auto'         => 'Auto-saisine',
    ],

    /*
    |--------------------------------------------------------------------------
    | Niveaux de comités (§4.2.2 du MGP)
    |--------------------------------------------------------------------------
    */
    'committee_levels' => [
        'cbgp' => 'CBGP - Comité de Base de Gestion des Plaintes',
        'ccgp' => 'CCGP - Comité Communal de Gestion des Plaintes',
        'cngp' => 'CNGP - Comité National de Gestion des Plaintes',
    ],

    /*
    |--------------------------------------------------------------------------
    | Statuts de plainte (workflow §4.4)
    |--------------------------------------------------------------------------
    */
    'complaint_statuses' => [
        'registered'   => 'Enregistrée',
        'under_review' => 'En examen',
        'accepted'     => 'Recevable',
        'rejected'     => 'Rejetée',
        'in_mediation' => 'En médiation',
        'resolved'     => 'Résolue',
        'escalated'    => 'Escaladée',
        'closed'       => 'Clôturée',
    ],

    /*
    |--------------------------------------------------------------------------
    | Composantes PACIPA (1 à 5)
    |--------------------------------------------------------------------------
    */
    'project_components' => [
        1 => 'C1 - Production agricole résiliente et production animale',
        2 => 'C2 - Marchés agricoles et d\'élevage',
        3 => 'C3 - Accès au financement rural',
        4 => 'C4 - Coordination et renforcement des capacités',
        5 => 'C5 - Gestion de projet',
    ],

    /*
    |--------------------------------------------------------------------------
    | Niveaux de risque des sites
    |--------------------------------------------------------------------------
    */
    'risk_levels' => [
        'low'      => 'Faible',
        'medium'   => 'Moyen',
        'high'     => 'Élevé',
        'critical' => 'Critique',
    ],

    /*
    |--------------------------------------------------------------------------
    | Régions du Niger (8 zones d'intervention PACIPA)
    |--------------------------------------------------------------------------
    */
    'regions' => [
        'agadez'    => 'Agadez',
        'diffa'     => 'Diffa',
        'dosso'     => 'Dosso',
        'maradi'    => 'Maradi',
        'niamey'    => 'Niamey',
        'tahoua'    => 'Tahoua',
        'tillaberi' => 'Tillabéri',
        'zinder'    => 'Zinder',
    ],

    /*
    |--------------------------------------------------------------------------
    | Numéro d'enregistrement format : REGION-COMMUNE-AAAA-NNNN
    |--------------------------------------------------------------------------
    */
    'registration_number_format' => '{REGION}-{COMMUNE}-{YEAR}-{SEQ}',

    /*
    |--------------------------------------------------------------------------
    | Délais SLA par défaut (en jours - configurable via SlaSettings)
    |--------------------------------------------------------------------------
    */
    'sla_defaults' => [
        'cbgp_exam_days'          => 3,
        'cbgp_investigation_days' => 7,
        'ccgp_exam_days'          => 3,
        'ccgp_investigation_days' => 4,
        'cngp_exam_days'          => 7,
        'cngp_investigation_days' => 3,
        'vbg_ugp_notification_hours'   => 24,
        'vbg_referral_hours'           => 72,
        'vbg_worldbank_eas_hours'      => 24,
        'vbg_worldbank_incident_hours' => 48,
        'vbg_enquiry_days'             => 30,
    ],
];
