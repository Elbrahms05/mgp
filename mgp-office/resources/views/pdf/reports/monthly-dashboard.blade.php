<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <style>
        * { font-family: DejaVu Sans, sans-serif; }
        body { color: #1f2937; font-size: 11px; margin: 0; padding: 28px 34px; }
        .header { border-bottom: 3px solid {{ $project->primary_color ?: '#2e7d32' }}; padding-bottom: 10px; margin-bottom: 16px; }
        .header h1 { color: {{ $project->primary_color ?: '#2e7d32' }}; font-size: 17px; margin: 0 0 3px; }
        .header .sub { color: #6b7280; font-size: 10px; }
        h2 { font-size: 13px; color: #111827; border-bottom: 1px solid #e5e7eb; padding-bottom: 4px; margin: 18px 0 8px; }
        .kpis { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .kpis td { width: 25%; padding: 8px; border: 1px solid #e5e7eb; text-align: center; }
        .kpis .n { font-size: 20px; font-weight: bold; color: {{ $project->primary_color ?: '#2e7d32' }}; }
        .kpis .l { font-size: 9px; color: #6b7280; text-transform: uppercase; }
        table.data { width: 100%; border-collapse: collapse; margin: 6px 0; }
        table.data th, table.data td { padding: 6px 8px; border-bottom: 1px solid #e5e7eb; text-align: left; }
        table.data th { background: #f9fafb; font-size: 10px; text-transform: uppercase; color: #6b7280; }
        .footer { margin-top: 24px; border-top: 1px solid #e5e7eb; padding-top: 8px; color: #9ca3af; font-size: 9px; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <h1>{{ $project->name }} - {{ ($annual ?? false) ? 'Rapport annuel MGP' : 'Tableau de bord périodique' }}</h1>
        <div class="sub">
            {{ $project->donor }} · Projet {{ $project->project_id }} ·
            Période : {{ $from->format('d/m/Y') }} → {{ $to->format('d/m/Y') }}
        </div>
    </div>

    <h2>Indicateurs des plaintes</h2>
    <table class="kpis">
        <tr>
            <td><div class="n">{{ $kpis['total'] }}</div><div class="l">Total</div></td>
            <td><div class="n">{{ $kpis['in_progress'] }}</div><div class="l">En cours</div></td>
            <td><div class="n">{{ $kpis['resolved'] }}</div><div class="l">Résolues</div></td>
            <td><div class="n">{{ $kpis['closed'] }}</div><div class="l">Clôturées</div></td>
        </tr>
        <tr>
            <td><div class="n">{{ $kpis['escalated'] }}</div><div class="l">Escaladées</div></td>
            <td><div class="n">{{ $kpis['rejected'] }}</div><div class="l">Rejetées</div></td>
            <td><div class="n">{{ number_format($resolution, 1) }}%</div><div class="l">Taux résolution</div></td>
            <td>
                <div class="n">{{ $satisfaction['count'] > 0 ? number_format($satisfaction['avg_overall'], 1) . '/5' : '-' }}</div>
                <div class="l">Satisfaction</div>
            </td>
        </tr>
    </table>

    <h2>Répartition par type</h2>
    <table class="data">
        <tr><th>Type</th><th>Nombre</th></tr>
        @forelse ($byType['labels'] as $i => $label)
            <tr><td>{{ $label }}</td><td>{{ $byType['data'][$i] ?? 0 }}</td></tr>
        @empty
            <tr><td colspan="2">Aucune donnée sur la période.</td></tr>
        @endforelse
    </table>

    <h2>Répartition par canal</h2>
    <table class="data">
        <tr><th>Canal</th><th>Nombre</th></tr>
        @forelse ($byChannel['labels'] as $i => $label)
            <tr><td>{{ $label }}</td><td>{{ $byChannel['data'][$i] ?? 0 }}</td></tr>
        @empty
            <tr><td colspan="2">Aucune donnée sur la période.</td></tr>
        @endforelse
    </table>

    <h2>Délai moyen de résolution par niveau (jours)</h2>
    <table class="data">
        <tr><th>Niveau</th><th>Jours</th></tr>
        @forelse ($avgDays as $level => $days)
            <tr><td>{{ strtoupper($level) }}</td><td>{{ $days }}</td></tr>
        @empty
            <tr><td colspan="2">-</td></tr>
        @endforelse
    </table>

    <div class="footer">
        Généré automatiquement le {{ $generatedAt->format('d/m/Y H:i') }} - {{ $project->name }} · Confidentiel
    </div>
</body>
</html>
