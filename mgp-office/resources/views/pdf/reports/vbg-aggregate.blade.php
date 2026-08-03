<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 11px; margin: 0; padding: 28px 34px; }
        .header { border-bottom: 3px solid #7b1fa2; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { color: #7b1fa2; font-size: 17px; margin: 0 0 3px; }
        .header .sub { color: #6b7280; font-size: 10px; }
        .notice { background: #f3e5f5; border-left: 4px solid #7b1fa2; padding: 10px 14px; margin: 12px 0; font-size: 10px; }
        h2 { font-size: 13px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin: 18px 0 8px; }
        table.data { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.data th, table.data td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        table.data th { background: #f9fafb; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; color: #9ca3af; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $project->name }} - Rapport VBG agrégé trimestriel</h1>
        <div class="sub">
            {{ $project->donor }} · Projet {{ $project->project_id }} ·
            Période : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}
        </div>
    </div>

    <div class="notice">
        <strong>Données strictement agrégées et anonymisées.</strong> Ce rapport ne contient
        aucune information permettant d'identifier une survivante ou un auteur présumé,
        conformément à l'approche centrée sur la survivante et à la minimisation des données (ER-07).
    </div>

    <h2>Volumétrie</h2>
    <table class="data">
        <tr><th>Indicateur</th><th>Valeur</th></tr>
        <tr><td>Signalements reçus</td><td>{{ $vbg['total'] }}</td></tr>
        <tr><td>Directement par la survivante</td><td>{{ $vbg['direct_by_survivor_pct'] }}%</td></tr>
        <tr><td>Via un tiers</td><td>{{ $vbg['via_third_party_pct'] }}%</td></tr>
        <tr><td>Taux de référencement</td><td>{{ $vbg['referral_rate_pct'] }}%</td></tr>
    </table>

    <h2>Répartition par canal de signalement</h2>
    <table class="data">
        <tr><th>Canal</th><th>Nombre</th></tr>
        @forelse ($vbg['by_channel'] as $channel => $count)
            <tr><td>{{ $channel }}</td><td>{{ $count }}</td></tr>
        @empty
            <tr><td colspan="2">Aucune donnée sur la période.</td></tr>
        @endforelse
    </table>

    <h2>Services de référencement mobilisés</h2>
    <table class="data">
        <tr><th>Service</th><th>Nombre</th></tr>
        @forelse ($vbg['services_referral'] as $service => $count)
            <tr><td>{{ $service }}</td><td>{{ $count }}</td></tr>
        @empty
            <tr><td colspan="2">Aucune donnée sur la période.</td></tr>
        @endforelse
    </table>

    <div class="footer">
        Généré automatiquement le {{ $generatedAt->format('d/m/Y H:i') }} - Diffusion restreinte
    </div>
</body>
</html>
