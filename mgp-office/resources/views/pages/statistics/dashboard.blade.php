<x-layout.app title="Statistiques" 
description="Tableau de bord analytique - MGP PACIPA"
icon="chart">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Statistiques</li>
    </x-slot>


    {{-- KPIs --}}
    <div class="row margin-row">
        @php
        $kpiCards = [
            ['value' => $kpis['total'],       'label' => 'Total plaintes',   'class' => 'card-kpi-primary'],
            ['value' => $kpis['in_progress'], 'label' => 'En cours',         'class' => 'card-kpi-warning'],
            ['value' => $kpis['resolved'],    'label' => 'Résolues',         'class' => 'card-kpi-success'],
            ['value' => $kpis['escalated'],   'label' => 'Escaladées',       'class' => 'card-kpi-danger'],
            ['value' => $kpis['rejected'],    'label' => 'Rejetées',         'class' => 'card-kpi-dark'],
            ['value' => $resolutionRate.'%',  'label' => 'Taux résolution',  'class' => 'card-kpi-primary'],
            ['value' => $slaBreaches->count(),'label' => 'SLA dépassés',     'class' => $slaBreaches->count() > 0 ? 'card-kpi-danger' : 'card-kpi-success'],
            ['value' => $committeeStats['active'].'/'.$committeeStats['total'], 'label' => 'Comités actifs', 'class' => 'card-kpi-dark'],
        ];
        @endphp
        @foreach($kpiCards as $kpi)
        <div class="col-6 col-md-3">
            <div class="card card-kpi {{ $kpi['class'] }}">
                <div class="kpi-value">{{ $kpi['value'] }}</div>
                <div class="kpi-label">{{ $kpi['label'] }}</div>
            </div>
        </div>
        @endforeach
    </div>
    {{-- Indicateurs comités --}}
    <div class="card mt-3" style="border-radius:8px;">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Comités de gestion des plaintes</h6>
            <div class="row g-3">
                @foreach([
                    ['label' => 'CBGP (Base)',    'count' => $committeeStats['cbgp']],
                    ['label' => 'CCGP (Communal)', 'count' => $committeeStats['ccgp']],
                    ['label' => 'CNGP (National)', 'count' => $committeeStats['cngp']],
                    ['label' => 'Actifs',           'count' => $committeeStats['active']],
                ] as $cs)
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#e8f5e9;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#009A44;">{{ $cs['count'] }}</div>
                        <div style="font-size:0.75rem;color:#009A44;">{{ $cs['label'] }}</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Indicateurs VBG agrégés --}}
    @if($vbgStats !== null)
    <div class="card mt-3" style="border-radius:8px;border-left:4px solid #DC3545;">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">
                <i data-feather="lock" style="width:16px;height:16px;" class="me-1 text-danger"></i>
                Indicateurs VBG/EAS/HS - Données agrégées anonymisées
            </h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#ffebee;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#DC3545;">{{ $vbgStats['total'] }}</div>
                        <div style="font-size:0.75rem;color:#b71c1c;">Signalements reçus</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#fce4ec;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#ad1457;">{{ $vbgStats['anonymous'] }}</div>
                        <div style="font-size:0.75rem;color:#880e4f;">Anonymes</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#e8eaf6;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#283593;">{{ $vbgStats['referred'] }}</div>
                        <div style="font-size:0.75rem;color:#1a237e;">Référencées</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#e0f2f1;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#00695c;">{{ $vbgStats['referral_rate'] }}%</div>
                        <div style="font-size:0.75rem;color:#004d40;">Taux référencement</div>
                    </div>
                </div>
            </div>
            <p class="text-muted mt-3 mb-0" style="font-size:0.78rem;">
                <i data-feather="shield" style="width:12px;height:12px;" class="me-1"></i>
                Accès restreint : coordonnateur UGP et spécialiste VBG uniquement. Aucune donnée identifiante.
            </p>
        </div>
    </div>
    @endif
    {{-- Délais moyens --}}
    @if(!empty($avgDelays))
    <div class="card mt-3" style="border-radius:8px;">
        <div class="card-body">
            <h6 class="fw-semibold mb-3">Délai moyen de résolution par niveau</h6>
            <div class="row g-3">
                @foreach($avgDelays as $level => $data)
                <div class="col-6 col-md-3">
                    <div style="text-align:center;padding:12px;background:#f5f5f5;border-radius:8px;">
                        <div style="font-size:1.6rem;font-weight:700;color:#E05206;">{{ $data['avg_days'] ?? 0 }}j</div>
                        <div style="font-size:0.75rem;color:#757575;">{{ strtoupper($level) ?? '-' }}</div>
                        <div style="font-size:0.7rem;color:#9e9e9e;">{{ $data['cnt'] ?? 0 }} plainte(s)</div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
    {{-- Alertes SLA --}}
    @if($slaBreaches->isNotEmpty())
    <div class="card mb-3" style="border-radius:8px;border-left:4px solid #DC3545;">
        <div class="card-body" style="padding:16px 24px;background:#fff8f8;">
            <h6 style="color:#DC3545;">
                <i data-feather="alert-triangle" style="width:16px;height:16px;" class="me-1"></i>
                {{ $slaBreaches->count() }} plainte(s) en dépassement SLA
            </h6>
            <div class="table-responsive">
            <table class="table table-sm" style="font-size:0.85rem;margin:0;">
                <thead>
                    <tr><th>N° Plainte</th><th>Type</th><th>Statut</th><th>Échéance</th></tr>
                </thead>
                <tbody>
                    @foreach($slaBreaches->take(5) as $c)
                    <tr>
                        <td><a href="{{ route('complaints.show', $c) }}" style="color:#E05206;">{{ $c->registration_number }}</a></td>
                        <td>{{ $c->complaintType?->label }}</td>
                        <td>{{ $c->status->label() }}</td>
                        <td class="text-danger">{{ $c->sla_deadline?->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @if($slaBreaches->count() > 5)
            <a href="{{ route('complaints.index', ['sla_breach' => 1]) }}" class="btn btn-link text-danger p-0 mt-2">
                Voir toutes les {{ $slaBreaches->count() }} plaintes en dépassement
            </a>
            @endif
        </div>
    </div>
    @endif

    {{-- Sites critiques --}}
    @if($criticalSites->isNotEmpty())
    <div class="card mb-3" style="border-radius:8px;border-left:4px solid #E05206;background:#fef6f2;">
        <div class="card-body" style="padding:16px 24px;">
            <h6 style="color:#E05206;">
                <i data-feather="map-pin" style="width:16px;height:16px;" class="me-1"></i>
                Sites à risque critique ({{ $criticalSites->count() }})
            </h6>
            <div style="display:flex;flex-wrap:wrap;gap:8px;">
                @foreach($criticalSites as $site)
                <a href="{{ route('sites.show', $site) }}"
                   style="background:#E05206;color:white;border-radius:4px;padding:6px 12px;font-size:0.8rem;text-decoration:none;">
                    {{ $site->code }} - {{ $site->name }}
                </a>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Graphiques principaux --}}
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold">Plaintes par type</h6>
                    <canvas id="chart-by-type" height="220"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold">Plaintes par canal</h6>
                    <canvas id="chart-by-channel" height="220"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-0">
        <div class="col-12 col-md-8">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold">Tendance mensuelle (12 mois)</h6>
                    <canvas id="chart-trend" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold">Par région</h6>
                    <canvas id="chart-by-region" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>


    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof Chart === 'undefined') return;

        const colors = ['#E05206','#009A44','#F97B3D','#DC3545','#6C757D','#17A2B8','#1C1C2E','#F97B3D'];

        new Chart(document.getElementById('chart-by-type'), {
            type: 'bar',
            data: {
                labels: @json($byType['labels']),
                datasets: [{ label: 'Plaintes', data: @json($byType['data']), backgroundColor: '#E05206' }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        new Chart(document.getElementById('chart-by-channel'), {
            type: 'doughnut',
            data: {
                labels: @json($byChannel['labels']),
                datasets: [{ data: @json($byChannel['data']), backgroundColor: colors }]
            },
            options: { responsive: true }
        });

        new Chart(document.getElementById('chart-trend'), {
            type: 'line',
            data: {
                labels: @json($trend['labels']),
                datasets: [{
                    label: 'Plaintes',
                    data: @json($trend['data']),
                    borderColor: '#E05206',
                    backgroundColor: 'rgba(224,82,6,0.1)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
        });

        @if(!empty($byRegion['labels']))
        new Chart(document.getElementById('chart-by-region'), {
            type: 'bar',
            data: {
                labels: @json($byRegion['labels']),
                datasets: [{ label: 'Plaintes', data: @json($byRegion['data']), backgroundColor: '#009A44' }]
            },
            options: {
                responsive: true,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        @endif
    });
    </script>
    @endpush

</x-layout.app>
