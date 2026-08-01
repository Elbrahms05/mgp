<?php

return [
    'title'  => 'Comités de gestion des plaintes',
    'new'    => 'Nouveau comité',
    'list'   => 'Liste des comités',
    'evaluate' => 'Évaluation trimestrielle',

    'levels' => [
        'cbgp' => 'CBGP - Comité de Base (Localité)',
        'ccgp' => 'CCGP - Comité Communal',
        'cngp' => 'CNGP - Comité National / UGP',
    ],

    'roles' => [
        'president'       => 'Président(e)',
        'secretary'       => 'Secrétaire général',
        'vbg_focal_m'     => 'Point focal VBG (Homme)',
        'vbg_focal_f'     => 'Point focal VBG (Femme)',
        'women_rep'       => 'Représentante associations femmes',
        'youth_rep'       => 'Représentant(e) jeunes',
        'beneficiary_rep' => 'Représentant bénéficiaires',
        'pap_rep'         => 'Représentant PAP',
        'religious'       => 'Autorité religieuse',
        'ngo_rep'         => 'Représentant ONG locale',
    ],

    'sla' => [
        'cbgp' => 'CBGP : 3 jours examen + 7 jours investigation (13 jours total)',
        'ccgp' => 'CCGP : 3 jours examen + 4 jours investigation (10 jours total)',
        'cngp' => 'CNGP : 7 jours examen + 3 jours investigation (10 jours total)',
    ],

    'messages' => [
        'installed'  => 'Comité installé avec succès (PV d\'installation généré).',
        'evaluated'  => 'Évaluation trimestrielle enregistrée.',
        'vbg_rule'   => 'Chaque CBGP et CCGP doit comporter 2 points focaux VBG (1 homme + 1 femme).',
    ],
];
