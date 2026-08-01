<?php

return [
    'title'           => 'Plaintes',
    'new'             => 'Nouvelle plainte',
    'list'            => 'Liste des plaintes',
    'show'            => 'Détail de la plainte',
    'registration_number' => 'N° d\'enregistrement',

    'types' => [
        1 => 'Inaccessibilité aux informations',
        2 => 'Marchés et contrats',
        3 => 'Travaux de réhabilitation',
        4 => 'Conditions de travail',
        5 => 'Pertes / affectation biens physiques',
        6 => 'Octroi d\'équipements',
        7 => 'Formations',
        8 => 'VBG / EAS / HS / VCE',
    ],

    'status' => [
        'registered'   => 'Enregistrée',
        'under_review' => 'En examen',
        'accepted'     => 'Recevable',
        'rejected'     => 'Rejetée',
        'in_mediation' => 'En médiation',
        'resolved'     => 'Résolue',
        'escalated'    => 'Escaladée',
        'closed'       => 'Clôturée',
    ],

    'channels' => [
        'in_person'       => 'En personne',
        'phone'           => 'Téléphone',
        'sms'             => 'SMS',
        'whatsapp'        => 'WhatsApp',
        'email'           => 'Email',
        'mail'            => 'Courrier',
        'complaint_box'   => 'Boîte de réclamation',
        'hotline'         => 'Ligne verte',
        'auto_seizure'    => 'Auto-saisine',
        'ussd'            => 'USSD',
    ],

    'levels' => [
        'cbgp' => 'Niveau 1 - CBGP',
        'ccgp' => 'Niveau 2 - CCGP',
        'cngp' => 'Niveau 3 - CNGP',
    ],

    'fields' => [
        'complainant'       => 'Plaignant',
        'type'              => 'Type de plainte',
        'channel'           => 'Canal de réception',
        'site'              => 'Site concerné',
        'description'       => 'Description',
        'status'            => 'Statut',
        'created_at'        => 'Date d\'enregistrement',
        'sla_deadline'      => 'Échéance SLA',
        'assigned_to'       => 'Assignée à',
        'resolution_note'   => 'Note de résolution',
    ],

    'messages' => [
        'created'     => 'Plainte enregistrée avec succès. Accusé de réception envoyé.',
        'updated'     => 'Plainte mise à jour.',
        'escalated'   => 'Plainte escaladée au niveau supérieur.',
        'resolved'    => 'Plainte marquée comme résolue.',
        'closed'      => 'Plainte clôturée.',
        'sla_warning' => 'Attention : l\'échéance SLA approche.',
        'sla_breach'  => 'Dépassement SLA - action requise immédiatement.',
    ],
];
