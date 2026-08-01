<x-layout.app :title="$site->name . ' - Sites PACIPA'">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
        <li class="breadcrumb-item active">{{ $site->name }}</li>
    </x-slot>

    @php
        $riskBg = ['low' => '#009A44', 'medium' => '#F97B3D', 'high' => '#E05206', 'critical' => '#DC3545'];
        $riskLabels = ['low' => 'Risque faible', 'medium' => 'Risque moyen', 'high' => 'Risque élevé', 'critical' => 'Risque critique'];
        $rc = $riskBg[$site->risk_level] ?? '#6C757D';
        $rl = $riskLabels[$site->risk_level] ?? $site->risk_level;
    @endphp

    {{-- Header --}}
    <div class="card white-text margin-row" style="border-radius:8px;margin-bottom:0;background:{{ $rc }};color:white;">
        <div class="card-body" style="padding:20px 24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="font-size:0.8rem;opacity:0.8;margin-bottom:4px;">
                        <code style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:4px;">{{ $site->code }}</code>
                    </div>
                    <h5 style="margin:0 0 4px;color:white;">{{ $site->name }}</h5>
                    <div style="opacity:0.85;font-size:0.9rem;color:white;">
                        {{ $site->siteType?->label ?? '-' }}
                        &bull;
                        {{ $site->locality?->name ?? '-' }},
                        {{ $site->locality?->commune?->department?->region?->name ?? '' }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:1.1rem;font-weight:700;margin-bottom:4px;color:white;">
                        <i data-feather="alert-triangle" style="width:16px;height:16px;"></i> {{ $rl }}
                    </div>
                    <div style="font-size:0.85rem;opacity:0.8;color:white;">{{ $site->status->label() }}</div>
                    <div style="margin-top:8px;">
                        @can('sites.update')
                        <a href="{{ route('sites.edit', $site) }}"
                           class="btn btn-sm" style="background:white;color:{{ $rc }};">
                            <i data-feather="edit-2" style="width:14px;height:14px;"></i> Modifier
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Flash --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
        <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Stats rapides --}}
    <div class="row g-3 mt-2 mb-0">
        <div class="col-6 col-md-3">
            <div class="card card-kpi card-kpi-primary">
                <div class="kpi-value">{{ $site->total_complaints_count }}</div>
                <div class="kpi-label">Plaintes total</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-kpi card-kpi-warning">
                <div class="kpi-value">{{ $site->open_complaints_count }}</div>
                <div class="kpi-label">En cours</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-kpi card-kpi-success">
                <div class="kpi-value">{{ $site->resolved_complaints_count }}</div>
                <div class="kpi-label">Résolues</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-kpi card-kpi-dark">
                <div class="kpi-value">{{ $site->contractors->count() }}</div>
                <div class="kpi-label">Entreprises</div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div x-data="{ activeTab: 'info' }" style="margin-top:16px;">

        <div class="card" style="border-radius:8px 8px 0 0;margin-bottom:0;">
            <div class="card-body p-0">
                <ul class="nav nav-tabs" style="border-bottom:none;padding:0 8px;">
                    <li class="nav-item">
                        <a href="#" class="nav-link" :class="activeTab==='info' ? 'active' : ''"
                           @click.prevent="activeTab='info'">
                            <i data-feather="info" style="width:14px;height:14px;"></i> Informations
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" :class="activeTab==='complaints' ? 'active' : ''"
                           @click.prevent="activeTab='complaints'">
                            <i data-feather="alert-triangle" style="width:14px;height:14px;"></i>
                            Plaintes
                            @if($site->open_complaints_count > 0)
                            <span class="badge ms-1" style="background:#DC3545;border-radius:50%;font-size:0.7rem;">
                                {{ $site->open_complaints_count }}
                            </span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" :class="activeTab==='contractors' ? 'active' : ''"
                           @click.prevent="activeTab='contractors'">
                            <i data-feather="tool" style="width:14px;height:14px;"></i> Entreprises
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="#" class="nav-link" :class="activeTab==='indicators' ? 'active' : ''"
                           @click.prevent="activeTab='indicators'">
                            <i data-feather="bar-chart-2" style="width:14px;height:14px;"></i> Indicateurs
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        {{-- Tab : Informations --}}
        <div x-show="activeTab==='info'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #E05206;">
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width:45%;">Composante PACIPA</td>
                                    <td>{{ $site->category->label() }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Statut</td>
                                    <td>{{ $site->status->label() }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Comité rattaché</td>
                                    <td>
                                        @if($site->committee)
                                        <a href="{{ route('committees.show', $site->committee) }}" style="color:#E05206;">
                                            {{ $site->committee->name }}
                                        </a>
                                        @else
                                        <span class="text-muted">Aucun</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Responsable UGP</td>
                                    <td>{{ $site->responsibleUser?->name ?? '-' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-12 col-md-6">
                        <table class="table table-striped">
                            <tbody>
                                <tr>
                                    <td class="text-muted" style="width:45%;">Date début</td>
                                    <td>{{ $site->start_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date fin prévue</td>
                                    <td>{{ $site->expected_end_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Date fin réelle</td>
                                    <td>{{ $site->actual_end_date?->format('d/m/Y') ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Superficie</td>
                                    <td>{{ $site->area_hectares ? number_format((float)$site->area_hectares, 2).' ha' : '-' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Bénéficiaires</td>
                                    <td>{{ $site->beneficiaries_count ? number_format($site->beneficiaries_count) : '-' }}</td>
                                </tr>
                                @if($site->latitude && $site->longitude)
                                <tr>
                                    <td class="text-muted">Coordonnées GPS</td>
                                    <td style="font-size:0.85rem;">
                                        {{ $site->latitude }}, {{ $site->longitude }}
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($site->description)
                <hr style="margin:16px 0;">
                <p class="text-muted">{{ $site->description }}</p>
                @endif
            </div>
        </div>

        {{-- Tab : Plaintes --}}
        <div x-show="activeTab==='complaints'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #F97B3D;">
            <div class="card-body p-0">
                @if($recentComplaints->isEmpty())
                <div style="text-align:center;padding:40px;color:#9e9e9e;">
                    <i data-feather="check-circle" style="width:40px;height:40px;"></i>
                    <p>Aucune plainte enregistrée pour ce site.</p>
                    @can('complaints.create')
                    <a href="{{ route('complaints.create', ['site_id' => $site->id]) }}" class="btn btn-primary btn-sm">
                        Enregistrer une plainte
                    </a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>N° Plainte</th>
                            <th>Type</th>
                            <th>Statut</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentComplaints as $complaint)
                        <tr>
                            <td><code>{{ $complaint->registration_number ?? '#'.$complaint->id }}</code></td>
                            <td>{{ $complaint->complaintType?->label ?? '-' }}</td>
                            <td>
                                <span class="text-muted" style="font-size:0.85rem;">
                                    {{ $complaint->status->label() }}
                                </span>
                            </td>
                            <td>{{ $complaint->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-link btn-sm">
                                    <i data-feather="eye" style="width:16px;height:16px;"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @if($site->total_complaints_count > 10)
                <div class="text-center p-3">
                    <a href="{{ route('complaints.index', ['site_id' => $site->id]) }}" class="btn btn-link" style="color:#E05206;">
                        Voir toutes les {{ $site->total_complaints_count }} plaintes
                    </a>
                </div>
                @endif
                @endif
            </div>
        </div>

        {{-- Tab : Entreprises --}}
        <div x-show="activeTab==='contractors'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #37474f;">
            <div class="card-body p-0">
                @if($site->contractors->isEmpty())
                <div style="text-align:center;padding:40px;color:#9e9e9e;">
                    <i data-feather="tool" style="width:40px;height:40px;"></i>
                    <p>Aucune entreprise enregistrée pour ce site.</p>
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Entreprise</th>
                            <th>N° Contrat</th>
                            <th>Montant (FCFA)</th>
                            <th>Période</th>
                            <th class="text-center">Actif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($site->contractors as $contractor)
                        <tr>
                            <td><strong>{{ $contractor->name }}</strong></td>
                            <td><code style="font-size:0.85rem;">{{ $contractor->contract_number ?? '-' }}</code></td>
                            <td>{{ $contractor->contract_amount ? number_format((float)$contractor->contract_amount, 0, ',', ' ') : '-' }}</td>
                            <td style="font-size:0.85rem;">
                                {{ $contractor->start_date?->format('d/m/Y') ?? '-' }}
                                →
                                {{ $contractor->end_date?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="text-center">
                                @if($contractor->is_active)
                                <i data-feather="check-circle" style="width:16px;height:16px;color:#009A44;"></i>
                                @else
                                <i data-feather="x" style="width:16px;height:16px;color:#6C757D;"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Tab : Indicateurs --}}
        <div x-show="activeTab==='indicators'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #009A44;">
            <div class="card-body">
                @if($complaintsByType->isEmpty())
                <div style="text-align:center;padding:40px;color:#9e9e9e;">
                    <i data-feather="bar-chart-2" style="width:40px;height:40px;"></i>
                    <p>Aucune donnée disponible.</p>
                </div>
                @else
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted">Plaintes par type</h6>
                        <canvas id="chart-by-type" height="200"></canvas>
                    </div>
                    <div class="col-12 col-md-6">
                        <h6 class="text-muted">Détail par type</h6>
                        <table class="table table-striped">
                            <tbody>
                                @foreach($complaintsByType as $row)
                                <tr>
                                    <td>{{ $row->complaintType?->label ?? 'Type #'.$row->complaint_type_id }}</td>
                                    <td class="text-end fw-bold">{{ $row->count }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                @endif
            </div>
        </div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        @if($complaintsByType->isNotEmpty())
        if (typeof Chart !== 'undefined') {
            new Chart(document.getElementById('chart-by-type'), {
                type: 'bar',
                data: {
                    labels: @json($complaintsByType->map(fn($r) => $r->complaintType?->label ?? 'Type '.$r->complaint_type_id)),
                    datasets: [{
                        label: 'Plaintes',
                        data: @json($complaintsByType->pluck('count')),
                        backgroundColor: '#E05206'
                    }]
                },
                options: {
                    responsive: true,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
                }
            });
        }
        @endif
    });
    </script>
    @endpush

</x-layout.app>
