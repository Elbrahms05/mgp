<x-layout.app title="Sites PACIPA - MGP"
    description="Sites du projet PACIPA"
    icon="home">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Sites</li>
    </x-slot>
    <x-slot name="actions">
        @can('sites.create')
        <a href="{{ route('sites.create') }}" class="btn btn-success">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouveau site
        </a>
        @endcan
        <a href="{{ route('sites.map') }}" class="btn btn-primary ms-2">
            <i data-feather="map" style="width:16px;height:16px;"></i> Carte
        </a>
    </x-slot>
    <div x-data="siteFilters()" x-init="init()" class="margin-row">



        {{-- Filtres --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('sites.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-3">
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" id="search" name="search"
                                    value="{{ request('search') }}"
                                    placeholder="Nom ou code..." class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_status" class="form-label">Statut</label>
                                <select name="status" id="filter_status" class="form-select">
                                    <option value="">Tous</option>
                                    @foreach(\App\Domains\Site\Enums\SiteStatus::cases() as $s)
                                    <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                                        {{ $s->label() }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_risk" class="form-label">Niveau de risque</label>
                                <select name="risk_level" id="filter_risk" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="low" {{ request('risk_level') === 'low'      ? 'selected' : '' }}>Faible</option>
                                    <option value="medium" {{ request('risk_level') === 'medium'   ? 'selected' : '' }}>Moyen</option>
                                    <option value="high" {{ request('risk_level') === 'high'     ? 'selected' : '' }}>Élevé</option>
                                    <option value="critical" {{ request('risk_level') === 'critical' ? 'selected' : '' }}>Critique</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_type" class="form-label">Type</label>
                                <select name="site_type_id" id="filter_type" class="form-select">
                                    <option value="">Tous les types</option>
                                    @foreach($siteTypes as $type)
                                    <option value="{{ $type->id }}" {{ request('site_type_id') == $type->id ? 'selected' : '' }}>
                                        {{ $type->label }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-2">
                            <div class="mb-3">
                                <label for="filter_region" class="form-label">Région</label>
                                <select name="region_id" id="filter_region" class="form-select">
                                    <option value="">Toutes les régions</option>
                                    @foreach($regions as $region)
                                    <option value="{{ $region->id }}" {{ request('region_id') == $region->id ? 'selected' : '' }}>
                                        {{ $region->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-1 d-flex align-items-end pb-3">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="search" style="width:16px;height:16px;"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        {{-- Flash --}}
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Tableau --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($sites->isEmpty())
                <div style="text-align:center;padding:48px 16px;color:#9e9e9e;">
                    <i data-feather="map-pin" style="width:48px;height:48px;"></i>
                    <p>Aucun site trouvé.</p>
                    @can('sites.create')
                    <a href="{{ route('sites.create') }}" class="btn btn-success">Créer un site</a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="margin:0;">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Nom du site</th>
                                <th>Type</th>
                                <th>Localité / Région</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Risque</th>
                                <th class="text-center">Plaintes ouvertes</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sites as $site)
                            <tr>
                                <td><code style="font-size:0.8rem;">{{ $site->code }}</code></td>
                                <td>
                                    <a href="{{ route('sites.show', $site) }}" style="color:#E05206;max-width:200px;display:block;">
                                        {{ $site->name }}
                                    </a>
                                </td>
                                <td>{{ $site->siteType?->label ?? '-' }}</td>
                                <td>
                                    <span class="text-muted" style="font-size:0.85rem;">
                                        {{ $site->locality?->name ?? '-' }}
                                        <br>
                                        <small>{{ $site->locality?->commune?->department?->region?->name ?? '' }}</small>
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                    $statusColors = [
                                    'planned' => ['bg' => '#e3f2fd', 'color' => '#E05206'],
                                    'in_progress' => ['bg' => '#e8f5e9', 'color' => '#009A44'],
                                    'suspended' => ['bg' => '#fff3e0', 'color' => '#F97B3D'],
                                    'completed' => ['bg' => '#e0f2f1', 'color' => '#00897b'],
                                    'closed' => ['bg' => '#f5f5f5', 'color' => '#6C757D'],
                                    ];
                                    $sc = $statusColors[$site->status->value] ?? ['bg' => '#f5f5f5', 'color' => '#6C757D'];
                                    @endphp
                                    <span style="background:{{ $sc['bg'] }};color:{{ $sc['color'] }};border-radius:12px;padding:2px 10px;font-size:0.75rem;font-weight:600;">
                                        {{ $site->status->label() }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                    $riskColors = [
                                    'low' => '#009A44',
                                    'medium' => '#F97B3D',
                                    'high' => '#E05206',
                                    'critical' => '#DC3545',
                                    ];
                                    $riskLabels = [
                                    'low' => 'Faible',
                                    'medium' => 'Moyen',
                                    'high' => 'Élevé',
                                    'critical' => 'Critique',
                                    ];
                                    $rc = $riskColors[$site->risk_level] ?? '#6C757D';
                                    $rl = $riskLabels[$site->risk_level] ?? $site->risk_level;
                                    @endphp
                                    <strong style="color:{{ $rc }};">{{ $rl }}</strong>
                                </td>
                                <td class="text-center">
                                    @if($site->open_complaints_count > 0)
                                    <span class="badge rounded-circle" style="background:#DC3545;min-width:24px;height:24px;line-height:24px;display:inline-block;">
                                        {{ $site->open_complaints_count }}
                                    </span>
                                    @else
                                    <span class="text-muted">0</span>
                                    @endif
                                </td>
                                <td class="text-end" style="white-space:nowrap;">
                                    <a href="{{ route('sites.show', $site) }}" class="btn btn-link btn-sm" title="Voir">
                                        <i data-feather="eye" style="width:16px;height:16px;"></i>
                                    </a>
                                    @can('sites.update')
                                    <a href="{{ route('sites.edit', $site) }}" class="btn btn-link btn-sm text-warning" title="Modifier">
                                        <i data-feather="edit-2" style="width:16px;height:16px;"></i>
                                    </a>
                                    @endcan
                                    @can('sites.delete')
                                    <form method="POST" action="{{ route('sites.destroy', $site) }}"
                                        style="display:inline;"
                                        onsubmit="return confirm('Supprimer ce site ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-link btn-sm text-danger" title="Supprimer">
                                            <i data-feather="trash-2" style="width:16px;height:16px;"></i>
                                        </button>
                                    </form>
                                    @endcan
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @if($sites->hasPages())
            <div class="card-footer text-center">
                {{ $sites->links() }}
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
        function siteFilters() {
            return {
                init() {}
            };
        }
    </script>
    @endpush

</x-layout.app>