<x-layout.app :title="$committee->name . ' - Comités MGP'">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Comités</a></li>
        <li class="breadcrumb-item active">{{ $committee->name }}</li>
    </x-slot>

    @php
        $levelBg = ['cbgp' => '#E05206', 'ccgp' => '#009A44', 'cngp' => '#6f42c1'];
        $lc = $levelBg[$committee->level->value] ?? '#6C757D';
    @endphp

    {{-- Header --}}
    <div class="card margin-row" style="border-radius:8px;margin-bottom:0;background:{{ $lc }};color:white;">
        <div class="card-body" style="padding:20px 24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="font-size:0.8rem;opacity:0.8;margin-bottom:4px;text-transform:uppercase;letter-spacing:1px;">
                        {{ $committee->level->label() }}
                    </div>
                    <h5 style="margin:0 0 4px;">{{ $committee->name }}</h5>
                    @if($committee->locality)
                    <div style="opacity:0.85;font-size:0.9rem;">
                        <i data-feather="map-pin" style="width:14px;height:14px;"></i>
                        {{ $committee->locality->name }},
                        {{ $committee->locality->commune?->name }},
                        {{ $committee->locality->commune?->department?->region?->name }}
                    </div>
                    @endif
                </div>
                <div style="text-align:right;">
                    <div style="font-size:0.9rem;margin-bottom:8px;">
                        @if($committee->is_active)
                        <span style="background:rgba(255,255,255,0.2);padding:2px 12px;border-radius:12px;">Actif</span>
                        @else
                        <span style="background:rgba(0,0,0,0.2);padding:2px 12px;border-radius:12px;">Inactif</span>
                        @endif
                    </div>
                    <div style="font-size:0.85rem;opacity:0.8;">
                        Installé le {{ $committee->installation_date?->format('d/m/Y') ?? '-' }}
                    </div>
                    <div style="margin-top:8px;display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                        @can('committees.manage')
                        <a href="{{ route('committees.edit', $committee) }}"
                           class="btn btn-sm"
                           style="background:rgba(255,255,255,0.9);color:{{ $lc }};">
                            <i data-feather="edit-2" style="width:14px;height:14px;"></i> Modifier
                        </a>
                        @endcan
                        @can('committees.evaluate')
                        <a href="{{ route('committees.evaluate', $committee) }}"
                           class="btn btn-sm"
                           style="background:rgba(255,255,255,0.2);color:white;border:1px solid rgba(255,255,255,0.4);">
                            <i data-feather="bar-chart-2" style="width:14px;height:14px;"></i> Évaluation
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div x-data="{ activeTab: 'members' }" style="margin-top:16px;">

        <ul class="nav nav-tabs" style="background:white;border-radius:8px 8px 0 0;padding:0 16px;">
            <li class="nav-item">
                <a href="#" class="nav-link" :class="activeTab==='members' ? 'active' : ''"
                   @click.prevent="activeTab='members'">
                    <i data-feather="users" style="width:14px;height:14px;" class="me-1"></i>
                    Membres ({{ $committee->members->count() }})
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link" :class="activeTab==='evaluations' ? 'active' : ''"
                   @click.prevent="activeTab='evaluations'">
                    <i data-feather="bar-chart-2" style="width:14px;height:14px;" class="me-1"></i>
                    Évaluations ({{ $evaluations->count() }})
                </a>
            </li>
        </ul>

        {{-- Tab : Membres --}}
        <div x-show="activeTab==='members'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #E05206;">
            <div class="card-body p-0">
                @if($committee->members->isEmpty())
                <div style="text-align:center;padding:40px;color:#9e9e9e;">
                    <i data-feather="user" style="width:40px;height:40px;"></i>
                    <p>Aucun membre enregistré.</p>
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Nom complet</th>
                            <th>Rôle</th>
                            <th class="text-center">Genre</th>
                            <th>Téléphone</th>
                            <th>Mode désignation</th>
                            <th class="text-center">Point focal VBG</th>
                            <th class="text-center">Actif</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($committee->members->sortBy('full_name') as $member)
                        <tr>
                            <td><strong>{{ $member->full_name }}</strong></td>
                            <td>{{ $member->role }}</td>
                            <td class="text-center">
                                @if($member->gender === 'M')
                                <span style="color:#E05206;font-weight:600;">H</span>
                                @elseif($member->gender === 'F')
                                <span style="color:#e91e63;font-weight:600;">F</span>
                                @else
                                -
                                @endif
                            </td>
                            <td>{{ $member->phone ?? '-' }}</td>
                            <td class="text-muted" style="font-size:0.85rem;">{{ $member->election_mode }}</td>
                            <td class="text-center">
                                @if($member->is_vbg_focal_point)
                                <span style="color:#6f42c1;" title="Point focal VBG">
                                    <i data-feather="shield" style="width:16px;height:16px;"></i>
                                </span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($member->is_active)
                                <i data-feather="check-circle" style="width:16px;height:16px;color:#009A44;"></i>
                                @else
                                <i data-feather="x-circle" style="width:16px;height:16px;color:#6C757D;"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>

                {{-- Résumé points focaux VBG --}}
                @php
                    $vbgFocals = $committee->members->where('is_vbg_focal_point', true);
                    $vbgM = $vbgFocals->where('gender', 'M')->count();
                    $vbgF = $vbgFocals->where('gender', 'F')->count();
                @endphp
                <div style="padding:12px 16px;background:#f5f5f5;border-top:1px solid #e0e0e0;font-size:0.85rem;">
                    Points focaux VBG :
                    <span style="color:{{ $vbgM >= 1 ? '#009A44' : '#DC3545' }};">
                        <strong>{{ $vbgM }} homme(s)</strong>
                    </span>
                    &nbsp;•&nbsp;
                    <span style="color:{{ $vbgF >= 1 ? '#009A44' : '#DC3545' }};">
                        <strong>{{ $vbgF }} femme(s)</strong>
                    </span>
                    @if($vbgM < 1 || $vbgF < 1)
                    <span class="text-danger"> - <i data-feather="alert-triangle" style="width:14px;height:14px;"></i> Exigence NES-2 non satisfaite</span>
                    @endif
                </div>
                @endif
            </div>
        </div>

        {{-- Tab : Évaluations --}}
        <div x-show="activeTab==='evaluations'" class="card" style="border-radius:0 0 8px 8px;border-top:2px solid #009A44;">
            <div class="card-body p-0">
                @if($evaluations->isEmpty())
                <div style="text-align:center;padding:40px;color:#9e9e9e;">
                    <i data-feather="bar-chart-2" style="width:40px;height:40px;"></i>
                    <p>Aucune évaluation trimestrielle.</p>
                    @can('committees.evaluate')
                    <a href="{{ route('committees.evaluate', $committee) }}" class="btn btn-success btn-sm">
                        Faire une évaluation
                    </a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Période</th>
                            <th class="text-center">Membres</th>
                            <th class="text-center">Plaintes enregistrées</th>
                            <th class="text-center">Plaintes traitées</th>
                            <th class="text-center">Résolues localement</th>
                            <th class="text-center">Transmises</th>
                            <th>Soumis le</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($evaluations as $eval)
                        <tr>
                            <td>
                                {{ $eval->period_start?->format('d/m/Y') ?? '-' }}
                                →
                                {{ $eval->period_end?->format('d/m/Y') ?? '-' }}
                            </td>
                            <td class="text-center">
                                {{ $eval->members_operational }} / {{ $eval->members_total }}
                            </td>
                            <td class="text-center">{{ $eval->complaints_registered }}</td>
                            <td class="text-center">{{ $eval->complaints_processed }}</td>
                            <td class="text-center">{{ $eval->complaints_resolved_local }}</td>
                            <td class="text-center">{{ $eval->complaints_transmitted }}</td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                {{ $eval->submitted_at?->format('d/m/Y') ?? '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>
        </div>

    </div>

</x-layout.app>
