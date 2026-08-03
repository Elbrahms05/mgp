<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Accusé de réception {{ $complaint->registration_number }}</title>
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 12px; margin: 0; padding: 30px 40px; }
        .header { border-bottom: 3px solid #047857; padding-bottom: 12px; margin-bottom: 20px; }
        .header h1 { color: #047857; font-size: 18px; margin: 0 0 4px; }
        .header .sub { color: #6b7280; font-size: 11px; }
        .ref-box { background: #ecfdf5; border: 1px solid #047857; border-radius: 6px;
                   padding: 14px 18px; margin: 18px 0; }
        .ref-box .label { color: #065f46; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
        .ref-box .value { color: #047857; font-size: 22px; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin: 14px 0; }
        td { padding: 7px 6px; border-bottom: 1px solid #e5e7eb; vertical-align: top; }
        td.k { color: #6b7280; width: 38%; }
        td.v { color: #111827; font-weight: 600; }
        .note { background: #f9fafb; border-left: 4px solid #047857; padding: 12px 16px;
                margin-top: 18px; font-size: 11px; color: #374151; }
        .footer { margin-top: 30px; border-top: 1px solid #e5e7eb; padding-top: 10px;
                  color: #9ca3af; font-size: 10px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>PACIPA - Mécanisme de Gestion des Plaintes</h1>
        <div class="sub">République du Niger · Ministère de l'Agriculture et de l'Élevage · Projet P179272</div>
    </div>

    <p style="font-size:14px; font-weight:bold; margin:0 0 4px;">Accusé de réception de plainte</p>
    <p style="color:#6b7280; margin:0;">Délivré le {{ $issuedAt }}</p>

    <div class="ref-box">
        <div class="label">Numéro de suivi - à conserver</div>
        <div class="value">{{ $complaint->registration_number }}</div>
    </div>

    <p>Votre demande a bien été enregistrée. Ce document atteste de sa prise en compte
       par le Mécanisme de Gestion des Plaintes du projet PACIPA.</p>

    <table>
        <tr><td class="k">Date d'enregistrement</td><td class="v">{{ $registeredAt }}</td></tr>
        <tr><td class="k">Canal de dépôt</td><td class="v">{{ $channel }}</td></tr>
        <tr><td class="k">Objet</td><td class="v">{{ $subject }}</td></tr>
        @if($site)
        <tr><td class="k">Site concerné</td><td class="v">{{ $site }}</td></tr>
        @endif
        <tr><td class="k">Échéance de traitement prévue</td><td class="v">{{ $slaDeadline }}</td></tr>
    </table>

    <div class="note">
        <strong>Suivi de votre plainte :</strong> conservez le numéro de suivi ci-dessus.
        Il vous permet de connaître l'avancement de son traitement auprès du comité de
        gestion des plaintes, sans avoir à décliner votre identité.
        Le traitement est gratuit et confidentiel.
    </div>

    <div class="footer">
        Document généré automatiquement - PACIPA-MGP · {{ $issuedAt }}
    </div>
</body>
</html>
