<x-layout.app
    title="{{ Str::limit($campaign->title, 50) }}"
    description="{{ $campaignTypes[$campaign->type] ?? $campaign->type }}"
    icon="megaphone">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
        <li class="breadcrumb-item active">{{ Str::limit($campaign->title, 35) }}</li>
    </x-slot>

    @can('communication.manage')
    <x-slot name="actions">
        <a href="{{ route('communication.edit', $campaign) }}" class="btn btn-primary btn-sm">
            <i data-feather="edit-2" style="width:16px;height:16px;"></i> Modifier
        </a>
    </x-slot>
    @endcan

    @php
    $statusLabels = ['planned' => 'Planifiée', 'active' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée'];
    $badgeColor = match($campaign->status) {
        'planned'   => 'primary',
        'active'    => 'success',
        'completed' => 'secondary',
        'cancelled' => 'danger',
        default     => 'secondary'
    };
    @endphp

    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <span class="fw-semibold">Détails de la campagne</span>
                    <span class="badge bg-{{ $badgeColor }}">
                        {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                    </span>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-3">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-semibold" style="width:35%;">Type</td>
                                <td>{{ $campaignTypes[$campaign->type] ?? $campaign->type }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Période</td>
                                <td>
                                    {{ $campaign->start_date?->format('d/m/Y') ?? '-' }}
                                    @if($campaign->end_date) → {{ $campaign->end_date->format('d/m/Y') }} @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-semibold">Créée par</td>
                                <td>{{ $campaign->createdBy?->name ?? '-' }} le {{ $campaign->created_at->format('d/m/Y') }}</td>
                            </tr>
                            @if($campaign->target_audience)
                            <tr>
                                <td class="text-muted fw-semibold">Public cible</td>
                                <td>{{ $campaign->target_audience }}</td>
                            </tr>
                            @endif
                            @if($campaign->regions)
                            <tr>
                                <td class="text-muted fw-semibold">Régions</td>
                                <td>{{ implode(', ', (array) $campaign->regions) }}</td>
                            </tr>
                            @endif
                            @if($campaign->languages)
                            <tr>
                                <td class="text-muted fw-semibold">Langues</td>
                                <td>{{ implode(', ', array_map(fn($l) => $languages[$l] ?? $l, (array) $campaign->languages)) }}</td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <hr>
                    <p class="small text-muted fw-semibold mb-1">Description / Objectifs</p>
                    <p style="white-space:pre-wrap;font-size:0.9rem;">{{ $campaign->description }}</p>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
