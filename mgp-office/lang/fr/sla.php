<?php

return [
    'title'         => 'Délais SLA',
    'deadline'      => 'Échéance',
    'days_left'     => ':days jour(s) restant(s)',
    'hours_left'    => ':hours heure(s) restante(s)',
    'overdue'       => 'En retard',
    'overdue_by'    => 'Dépassé de :days jour(s)',
    'on_time'       => 'Dans les délais',
    'warning'       => 'Attention : échéance dans moins de 24h',

    'levels' => [
        'cbgp_total'       => '13 jours (CBGP)',
        'ccgp_total'       => '10 jours (CCGP)',
        'cngp_total'       => '10 jours (CNGP)',
        'vbg_ugp'          => '24h - Notification UGP',
        'vbg_referral'     => '72h - Référencement services VBG',
        'vbg_worldbank'    => '24h - Notification Banque mondiale (EAS/HS)',
        'incident_es'      => '48h - Notification Banque mondiale (incident E&S)',
        'vbg_enquiry'      => '30 jours - Commission d\'enquête VBG',
    ],

    'alerts' => [
        'approaching'  => 'L\'échéance SLA approche pour :count plainte(s).',
        'breached'     => ':count plainte(s) en dépassement SLA requièrent une action immédiate.',
        'critical'     => 'CRITIQUE - Délai VBG dépassé. Notification Banque mondiale obligatoire.',
    ],
];
