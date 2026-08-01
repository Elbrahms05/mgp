<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport plaintes - PACIPA MGP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #0DB02B; font-size: 16px; }
        h2 { color: #555; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #0DB02B; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; font-size: 11px; }
        tr:nth-child(even) { background: #f5f5f5; }
        .sla-breach { color: #c62828; font-weight: bold; }
        .footer { margin-top: 24px; color: #757575; font-size: 10px; border-top: 1px solid #e0e0e0; padding-top: 8px; }
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 10px; color: white; }
        .badge-resolved { background: #2e7d32; }
        .badge-closed { background: #546e7a; }
        .badge-rejected { background: #c62828; }
        .badge-default { background: #0DB02B; }
    </style>
</head>
<body>
    <h1>PACIPA - Mécanisme de Gestion des Plaintes</h1>
    <h2>Rapport plaintes - Période : {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}</h2>
    <p style="color:#555;">Généré le {{ now()->format('d/m/Y H:i') }} | {{ $complaints->count() }} plainte(s)</p>

    <table>
        <thead>
            <tr>
                <th>N° Plainte</th>
                <th>Type</th>
                <th>Statut</th>
                <th>Site</th>
                <th>Région</th>
                <th>Canal</th>
                <th>Date faits</th>
                <th>Enregistrement</th>
                <th>SLA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($complaints as $c)
            @php
            $isBreach = $c->sla_deadline && $c->sla_deadline->isPast()
                && !in_array($c->status->value, ['closed','rejected']);
            @endphp
            <tr class="{{ $isBreach ? 'sla-breach' : '' }}">
                <td><strong>{{ $c->registration_number }}</strong></td>
                <td>{{ $c->complaintType?->label ?? '-' }}</td>
                <td>{{ $c->status->label() }}</td>
                <td>{{ $c->site?->name ?? 'Hors site' }}</td>
                <td>{{ $c->site?->locality?->commune?->department?->region?->name ?? '-' }}</td>
                <td>{{ $c->channel?->label() ?? '-' }}</td>
                <td>{{ $c->incident_date?->format('d/m/Y') ?? '-' }}</td>
                <td>{{ $c->created_at->format('d/m/Y') }}</td>
                <td class="{{ $isBreach ? 'sla-breach' : '' }}">
                    {{ $c->sla_deadline?->format('d/m/Y') ?? '-' }}
                    @if($isBreach) ⚠️ @endif
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:#9e9e9e;">Aucune plainte pour cette période.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PACIPA - P179272 | Ministère de l'Agriculture et de l'Élevage, Niger | Rapport confidentiel
    </div>
</body>
</html>
