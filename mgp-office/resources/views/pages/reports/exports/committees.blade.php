<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport comités - PACIPA MGP</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; color: #333; }
        h1 { color: #0DB02B; font-size: 16px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th { background: #0DB02B; color: white; padding: 6px 8px; text-align: left; font-size: 11px; }
        td { padding: 5px 8px; border-bottom: 1px solid #e0e0e0; font-size: 11px; }
        tr:nth-child(even) { background: #f5f5f5; }
        .footer { margin-top: 24px; color: #757575; font-size: 10px; border-top: 1px solid #e0e0e0; padding-top: 8px; }
    </style>
</head>
<body>
    <h1>PACIPA MGP - Rapport comités de gestion des plaintes</h1>
    <p><strong>Période :</strong> {{ $from->format('d/m/Y') }} au {{ $to->format('d/m/Y') }}</p>
    <p>Généré le {{ now()->format('d/m/Y H:i') }} | {{ $committees->count() }} comité(s)</p>

    <table>
        <thead>
            <tr>
                <th>Comité</th>
                <th>Niveau</th>
                <th>Localité</th>
                <th>Région</th>
                <th>Membres</th>
                <th>Actif</th>
                <th>Date installation</th>
            </tr>
        </thead>
        <tbody>
            @forelse($committees as $c)
            <tr>
                <td><strong>{{ $c->name }}</strong></td>
                <td>{{ strtoupper($c->level->value ?? $c->level) }}</td>
                <td>{{ $c->locality?->name ?? '-' }}</td>
                <td>{{ $c->locality?->commune?->department?->region?->name ?? '-' }}</td>
                <td>{{ $c->members_count }}</td>
                <td>{{ $c->is_active ? 'Oui' : 'Non' }}</td>
                <td>{{ $c->installation_date?->format('d/m/Y') ?? '-' }}</td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:#9e9e9e;">Aucun comité enregistré.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        PACIPA - P179272 | Rapport comités MGP | Formulaire F06 (Annexe 3)
    </div>
</body>
</html>
