<x-layout.app title="Comités"
description="Comités de gestion des plaintes (CBGP, CCGP, CNGP)"
icon="users">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Comités</li>
    </x-slot>
    <x-slot name="actions">
         @can('committees.manage')
                <a href="{{ route('committees.create') }}" class="btn btn-warning btn-sm">
                    <i data-feather="users" style="width:16px;height:16px;"></i> Nouveau comité
                </a>
                @endcan
    </x-slot>
    <div x-data="committeeIndex()" x-init="init()" class="margin-row">

        

        {{-- Filtres --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('committees.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" id="search" name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nom du comité..." class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_level" class="form-label">Niveau</label>
                                <select name="level" id="filter_level" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(\App\Domains\Committee\Enums\CommitteeLevel::cases() as $l)
                                    <option value="{{ $l->value }}" {{ request('level') === $l->value ? 'selected' : '' }}>
                                        {{ $l->value }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_active" class="form-label">Statut</label>
                                <select name="is_active" id="filter_active" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-10 col-md-3">
                            <div class="mb-3">
                                <label for="filter_region" class="form-label">Région</label>
                                <select name="region_id" id="filter_region" class="form-select">
                                    <option value="">Toutes régions</option>
                                    @foreach($regions as $r)
                                    <option value="{{ $r->id }}" {{ request('region_id') == $r->id ? 'selected' : '' }}>
                                        {{ $r->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-2 col-md-1 d-flex align-items-end pb-3">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="search" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Tableau --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($committees->isEmpty())
                <div style="text-align:center;padding:48px;color:#9e9e9e;">
                    <i data-feather="users" style="width:48px;height:48px;"></i>
                    <p>Aucun comité trouvé.</p>
                    @can('committees.manage')
                    <a href="{{ route('committees.create') }}" class="btn btn-success">Créer un comité</a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Nom du comité</th>
                            <th>Localité / Commune</th>
                            <th class="text-center">Membres</th>
                            <th>Date installation</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($committees as $committee)
                        @php
                            $levelStyles = [
                                'cbgp' => ['bg' => '#e3f2fd', 'color' => '#E05206'],
                                'ccgp' => ['bg' => '#e0f2f1', 'color' => '#009A44'],
                                'cngp' => ['bg' => '#ede7f6', 'color' => '#6f42c1'],
                            ];
                            $ls = $levelStyles[$committee->level->value] ?? ['bg' => '#f5f5f5', 'color' => '#6C757D'];
                        @endphp
                        <tr>
                            <td>
                                <span style="background:{{ $ls['bg'] }};color:{{ $ls['color'] }};border-radius:12px;padding:2px 10px;font-size:0.75rem;font-weight:700;text-transform:uppercase;">
                                    {{ $committee->level->value }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('committees.show', $committee) }}" style="color:#E05206;">
                                    {{ $committee->name }}
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">
                                {{ $committee->locality?->name ?? '-' }}
                                @if($committee->locality?->commune)
                                <br><small>{{ $committee->locality->commune->name }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <strong>{{ $committee->members_count }}</strong>
                            </td>
                            <td>{{ $committee->installation_date?->format('d/m/Y') ?? '-' }}</td>
                            <td class="text-center">
                                @if($committee->is_active)
                                <span style="color:#009A44;font-size:0.85rem;">
                                    <i data-feather="check-circle" style="width:14px;height:14px;"></i> Actif
                                </span>
                                @else
                                <span class="text-muted" style="font-size:0.85rem;">
                                    <i data-feather="x-circle" style="width:14px;height:14px;"></i> Inactif
                                </span>
                                @endif
                            </td>
                            <td class="text-end" style="white-space:nowrap;">
                                <a href="{{ route('committees.show', $committee) }}" class="btn btn-link btn-sm" title="Voir">
                                    <i data-feather="eye" style="width:16px;height:16px;"></i>
                                </a>
                                @can('committees.manage')
                                <a href="{{ route('committees.edit', $committee) }}" class="btn btn-link btn-sm text-warning" title="Modifier">
                                    <i data-feather="edit-2" style="width:16px;height:16px;"></i>
                                </a>
                                @endcan
                                @can('committees.evaluate')
                                <a href="{{ route('committees.evaluate', $committee) }}" class="btn btn-link btn-sm" style="color:#009A44;" title="Évaluation">
                                    <i data-feather="bar-chart-2" style="width:16px;height:16px;"></i>
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>
            @if($committees->hasPages())
            <div class="card-footer text-center">
                {{ $committees->links() }}
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
    function committeeIndex() {
        return {
            init() {}
        };
    }
    </script>
    @endpush

</x-layout.app>
