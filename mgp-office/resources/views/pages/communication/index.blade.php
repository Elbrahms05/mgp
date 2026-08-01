<x-layout.app
    title="Communication"
    description="Campagnes de sensibilisation et d'information"
    icon="megaphone">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Communication</li>
    </x-slot>

    @can('communication.manage')
    <x-slot name="actions">
        <a href="{{ route('communication.create') }}" class="btn btn-success btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouvelle campagne
        </a>
    </x-slot>
    @endcan

    {{-- Filtres --}}
    <div class="card margin-row" style="border-radius:8px;">
        <div class="card-body" style="padding:16px 24px 8px;">
            <form method="GET" action="{{ route('communication.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" id="search" name="search" value="{{ request('search') }}"
                               placeholder="Titre, description..." class="form-control">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_type" class="form-label">Type</label>
                        <select name="type" id="filter_type" class="form-select">
                            <option value="">Tous types</option>
                            @foreach($campaignTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_status" class="form-label">Statut</label>
                        <select name="status" id="filter_status" class="form-select">
                            <option value="">Tous statuts</option>
                            @foreach(['planned' => 'Planifiée', 'active' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée'] as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="search" class="me-1" style="width:15px;height:15px;"></i>Rechercher
                        </button>
                        <a href="{{ route('communication.index') }}" class="btn btn-outline-secondary ms-1">
                            <i data-feather="x" class="me-1" style="width:15px;height:15px;"></i>Réinitialiser
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Campagnes Grid --}}
    <div class="row mt-3">
        @forelse($campaigns as $campaign)
        @php
        $statusColors = ['planned' => 'primary', 'active' => 'success', 'completed' => 'secondary', 'cancelled' => 'danger'];
        $statusLabels = ['planned' => 'Planifiée', 'active' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée'];
        @endphp
        <div class="col-12 col-md-6">
            <div class="card h-100" style="border-radius:8px;border-top:3px solid var(--niger-{{ $statusColors[$campaign->status] ?? 'primary' }});">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <h6 class="card-title mb-2">{{ $campaign->title }}</h6>
                        <span class="badge bg-{{ $statusColors[$campaign->status] ?? 'secondary' }}">
                            {{ $statusLabels[$campaign->status] ?? $campaign->status }}
                        </span>
                    </div>
                    <p class="text-muted small mb-2">
                        <i data-feather="tag" class="me-1" style="width:14px;height:14px;display:inline;"></i> 
                        {{ $campaignTypes[$campaign->type] ?? $campaign->type }}
                    </p>
                    <p class="text-muted small mb-2">
                        <i data-feather="calendar" class="me-1" style="width:14px;height:14px;display:inline;"></i>
                        {{ $campaign->start_date?->format('d/m/Y') }}
                        @if($campaign->end_date) → {{ $campaign->end_date->format('d/m/Y') }} @endif
                    </p>
                    @if($campaign->regions)
                    <p class="text-muted small mb-2">
                        <i data-feather="map-pin" class="me-1" style="width:14px;height:14px;display:inline;"></i>
                        {{ implode(', ', (array) $campaign->regions) }}
                    </p>
                    @endif
                    <p class="small mt-3">{{ Str::limit($campaign->description, 100) }}</p>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('communication.show', $campaign) }}" class="link-primary text-decoration-none small me-3">
                        Voir
                    </a>
                    @can('communication.manage')
                    <a href="{{ route('communication.edit', $campaign) }}" class="link-warning text-decoration-none small">
                        Modifier
                    </a>
                    @endcan
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card text-center" style="border-radius:8px;padding:48px;">
                <div class="card-body text-muted">
                    <i data-feather="send" style="width:48px;height:48px;display:block;margin:0 auto 16px;"></i>
                    <p>Aucune campagne de communication enregistrée.</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($campaigns->hasPages())
    <div class="d-flex justify-content-center mt-3">
        {{ $campaigns->links() }}
    </div>
    @endif

</x-layout.app>
