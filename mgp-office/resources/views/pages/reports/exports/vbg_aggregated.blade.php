<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport VBG agrégé - PACIPA MGP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #c62828; font-size: 16px; }
        .warning { background: #ffebee; border-left: 4px solid #c62828; padding: 8px 12px; margin: 12px 0; font-size: 11px; }
        table { width: 50%; border-collapse: collapse; margin-top: 12px; }
        th { background: #c62828; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; font-size: 11px; }
        tr:nth-child(even) { background: #fafafa; }
        .footer { margin-top: 24px; color: #757575; font-size: 10px; border-top: 1px solid #e0e0e0; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>PACIPA MGP - Rapport VBG/EAS/HS Trimestriel (Données agrégées anonymisées)</h1>
    <div class="warning">
        <strong>CONFIDENTIEL - ACCÈS RESTREINT</strong><br>
        Ce rapport contient des données agrégées uniquement. Aucune donnée identifiant une survivante n'est présente.
        Diffusion : Coordonnateur UGP, Spécialiste VBG, Banque mondiale uniquement.
    </div>

    <p><strong>Période :</strong> {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}</p>
    <p>Généré le {{ now()->format('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr><th>Indicateur</th><th>Valeur</th></tr>
        </thead>
        <tbody>
            <tr><td>Nombre total de signalements reçus</td><td><strong>{{ $total }}</strong></td></tr>
            <tr><td>Signalements anonymes</td><td>{{ $anonymous }}</td></tr>
            <tr><td>Signalements avec identité (consentement)</td><td>{{ $total - $anonymous }}</td></tr>
            <tr><td>Référencés vers services (médical, juridique, psychosocial)</td><td>{{ $referred }}</td></tr>
            <tr><td>Taux de référencement (%)</td><td>{{ $total > 0 ? round(($referred / $total) * 100, 1) : 0 }}%</td></tr>
            @foreach($byChannel as $channel => $count)
            <tr><td>Canal : {{ $channel }}</td><td>{{ $count }}</td></tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        PACIPA - P179272 | Rapport VBG agrégé anonymisé | GBVIMS conforme | Confidentiel
    </div>
</body>
</html>
