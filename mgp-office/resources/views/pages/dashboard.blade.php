<x-layout.app title="Tableau de bord"
description="Vue d'ensemble - Mécanisme de Gestion des Plaintes"
icon="home">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Tableau de bord</li>
    </x-slot>

    <!-- KPI Cards -->
    <div class="row margin-row">
        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#0DB02B;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $kpis['total'] }}</div>
                    <div class="small opacity-75">Total plaintes</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#e65100;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $kpis['in_progress'] }}</div>
                    <div class="small opacity-75">En cours</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#2e7d32;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $kpis['resolved'] }}</div>
                    <div class="small opacity-75">Résolues</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#c62828;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $kpis['escalated'] }}</div>
                    <div class="small opacity-75">Escaladées</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#546e7a;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $kpis['rejected'] }}</div>
                    <div class="small opacity-75">Rejetées</div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-4 col-lg-2">
            <div class="card text-white mb-4" style="background-color:#37474f;border-radius:8px;">
                <div class="card-body p-3">
                    <div class="fw-bold" style="font-size:1.75rem;">{{ $resolutionRate }}%</div>
                    <div class="small opacity-75">Taux résolution</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alertes SLA -->
    @if ($slaBreaches->isNotEmpty())
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-semibold mb-0 text-danger">
                            <i data-feather="alert-triangle" class="me-1" style="width:18px;height:18px;"></i>
                            {{ $slaBreaches->count() }} plainte(s) en dépassement SLA
                        </h5>
                        <a href="{{ route('complaints.index', ['status' => 'urgent']) }}" class="btn btn-sm btn-outline-danger">
                            Voir tout
                        </a>
                    </div>
                    <div class="table-responsive mt-3">
                        <table class="table table-hover align-middle" style="margin:0;font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <th>N° Plainte</th>
                                    <th>Type</th>
                                    <th>Statut</th>
                                    <th>Échéance SLA</th>
                                    <th class="text-end">Urgence</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($slaBreaches->take(8) as $complaint)
                                <tr>
                                    <td>
                                        <a href="{{ route('complaints.show', $complaint) }}" class="text-primary text-decoration-none">
                                            <strong>{{ $complaint->registration_number }}</strong>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary" style="font-size:0.75rem;">
                                            {{ $complaint->complaintType?->label }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $complaint->status->value }}">
                                            {{ $complaint->status->label() }}
                                        </span>
                                    </td>
                                    <td class="text-danger fw-semibold">
                                        {{ $complaint->sla_deadline?->format('d/m/Y H:i') }}
                                    </td>
                                    <td class="text-end">
                                        @php
                                            $hoursLeft = $complaint->sla_deadline?->diffInHours(now(), false) ?? 0;
                                            $urgencyClass = $hoursLeft < 0 ? 'text-danger' : ($hoursLeft < 24 ? 'text-warning' : 'text-muted');
                                        @endphp
                                        <span class="{{ $urgencyClass }} small">
                                            {{ abs($hoursLeft) }}h
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="row mt-4">
        <!-- Plaintes récentes -->
        <div class="col-12 col-lg-6">
            <div class="card" style="border-radius:8px;">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center" style="color: white;">
                    <h6 class="fw-semibold mb-0" style="color: white;">
                        <i data-feather="file-text" class="me-2" style="width:16px;height:16px;"></i>
                        Plaintes récentes
                    </h6>
                    <a href="{{ route('complaints.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    @if(isset($recentComplaints) && $recentComplaints->isNotEmpty())
                    <div style="max-height:350px;overflow-y:auto;">
                        @foreach($recentComplaints->take(8) as $complaint)
                        <div class="d-flex align-items-start border-bottom p-3" style="gap:12px;">
                            <div>
                                <div class="fw-semibold text-dark">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="text-decoration-none">
                                        {{ $complaint->registration_number }}
                                    </a>
                                </div>
                                <small class="text-muted d-block">
                                    {{ $complaint->complaintType?->label }} · 
                                    {{ $complaint->created_at->diffForHumans() }}
                                </small>
                                <small class="text-muted d-block">
                                    <i data-feather="map-pin" style="width:12px;height:12px;display:inline;" class="me-1"></i>
                                    {{ $complaint->site?->name ?? 'Hors-site' }}
                                </small>
                            </div>
                            <div class="ms-auto">
                                <span class="badge bg-{{ $complaint->status->value }}">
                                    {{ $complaint->status->label() }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <i data-feather="inbox" style="width:32px;height:32px;margin-bottom:12px;display:block;"></i>
                        <p class="mb-0">Aucune plainte récente</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Activité VBG (si accessible) -->
        <div class="col-12 col-lg-6">
            <div class="card" style="border-radius:8px;">
                <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold mb-0" style="color: white;">
                        <i data-feather="shield-alert" class="me-2" style="width:16px;height:16px;color:#c62828;"></i>
                        Plaintes VBG/EAS/HS
                    </h6>
                    @can('vbg.view')
                    <a href="{{ route('vbg.index') }}" class="btn btn-sm btn-outline-danger">Accéder</a>
                    @endcan
                </div>
                <div class="card-body">
                    @can('vbg.view')
                        @if(isset($vbgStats))
                        <div class="row g-3">
                            <div class="col-6">
                                <div class="p-3 border border-danger border-opacity-25 rounded-3" style="background-color:#f5f5f5;">
                                    <div class="fw-semibold text-danger">{{ $vbgStats['pending'] ?? 0 }}</div>
                                    <small class="text-muted">En attente d'orientation</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border border-success border-opacity-25 rounded-3" style="background-color:#f5f5f5;">
                                    <div class="fw-semibold text-success">{{ $vbgStats['referred'] ?? 0 }}</div>
                                    <small class="text-muted">Orientées aux services</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border border-warning border-opacity-25 rounded-3" style="background-color:#f5f5f5;">
                                    <div class="fw-semibold text-warning">{{ $vbgStats['follow_up'] ?? 0 }}</div>
                                    <small class="text-muted">En suivi assiduité</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 border border-info border-opacity-25 rounded-3" style="background-color:#f5f5f5;">
                                    <div class="fw-semibold text-info">{{ $vbgStats['closed'] ?? 0 }}</div>
                                    <small class="text-muted">Dossiers clos</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    @else
                    <div class="alert alert-warning mb-0">
                        <small>Accès VBG non autorisé pour votre rôle.</small>
                    </div>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Actions rapides -->
        <div class="col-md-12">
            <div class="card" style="border-radius:8px;">
                <div class="card-header border-bottom">
                    <h6 class="fw-semibold mb-0" style="color: white;">
                        <i data-feather="zap" class="me-2" style="width:16px;height:16px;"></i>
                        Actions rapides
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex gap-3 flex-wrap">
                        <a href="{{ route('complaints.create') }}" class="btn btn-success">
                            <i data-feather="plus" style="width:16px;height:16px;" class="me-2"></i>
                            Nouvelle plainte
                        </a>
                        <a href="{{ route('complaints.index', ['status' => 'pending_review']) }}" class="btn btn-warning">
                            <i data-feather="eye" style="width:16px;height:16px;" class="me-2"></i>
                            À examiner
                        </a>
                        <a href="{{ route('workers.index') }}" class="btn btn-info">
                            <i data-feather="users" style="width:16px;height:16px;" class="me-2"></i>
                            Équipe
                        </a>
                        <a href="{{ route('statistics.dashboard') }}" class="btn btn-outline-primary">
                            <i data-feather="bar-chart-2" style="width:16px;height:16px;" class="me-2"></i>
                            Statistiques & Graphiques
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Résumé par région -->
        <div class="col-md-12">
            <div class="card" style="border-radius:8px;">
                <div class="card-header border-bottom">
                    <h6 class="fw-semibold mb-0" style="color: white;">
                        <i data-feather="map" class="me-2" style="width:16px;height:16px;"></i>
                        Plaintes par région
                    </h6>
                </div>
                <div class="card-body p-0">
                    @if(isset($byRegion) && $byRegion->isNotEmpty())
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <th>Région</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">En cours</th>
                                    <th class="text-center">Résolues</th>
                                    <th class="text-center">% Résolution</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($byRegion as $region)
                                <tr>
                                    <td class="text-truncate">{{ $region->name ?? '-' }}</td>
                                    <td class="text-center fw-semibold">{{ $region->total_complaints ?? 0 }}</td>
                                    <td class="text-center"><span class="badge bg-warning">{{ $region->in_progress ?? 0 }}</span></td>
                                    <td class="text-center"><span class="badge bg-success">{{ $region->resolved ?? 0 }}</span></td>
                                    <td class="text-center">
                                        @php
                                            $rate = $region->total_complaints ? round(($region->resolved ?? 0) / $region->total_complaints * 100, 0) : 0;
                                        @endphp
                                        <span class="text-{{ $rate >= 75 ? 'success' : ($rate >= 50 ? 'warning' : 'danger') }} fw-semibold">
                                            {{ $rate }}%
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="p-4 text-center text-muted">
                        <p class="mb-0">Données régionales non disponibles</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
