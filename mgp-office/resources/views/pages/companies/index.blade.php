<x-layout.app
    title="Entreprises"
    description="Gestion des entreprises intervenant sur les sites PACIPA"
    icon="briefcase">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Entreprises</li>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('companies.create') }}" class="btn btn-success btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouvelle entreprise
        </a>
    </x-slot>

    {{-- Filtres --}}
    <div class="card margin-row">
        <div class="card-body" style="padding:16px 24px 8px;">
            <form method="GET" action="{{ route('companies.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-4">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" id="search" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="Nom, N° RCCM, email...">
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="sector" class="form-label">Secteur</label>
                        <select name="sector" id="sector" class="form-select">
                            <option value="">Tous secteurs</option>
                            @foreach($sectors as $sect)
                            <option value="{{ $sect }}" {{ request('sector') === $sect ? 'selected' : '' }}>
                                {{ $sect }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label for="is_active" class="form-label">Statut</label>
                        <select name="is_active" id="is_active" class="form-select">
                            <option value="">Tous</option>
                            <option value="true" {{ request('is_active') === 'true' ? 'selected' : '' }}>Actives</option>
                            <option value="false" {{ request('is_active') === 'false' ? 'selected' : '' }}>Inactives</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="search" style="width:15px;height:15px;" class="me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary ms-1">
                            <i data-feather="x" style="width:15px;height:15px;"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau --}}
    <div class="card">
        <div class="card-body p-0">
            @if($companies->isEmpty())
            <div class="text-center text-muted" style="padding:48px;">
                <i data-feather="briefcase" style="width:48px;height:48px;display:block;margin:0 auto 16px;"></i>
                <p>Aucune entreprise enregistrée.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>N° RCCM</th>
                            <th>Secteur</th>
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th class="text-center">Sites</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($companies as $company)
                        <tr>
                            <td class="fw-semibold">
                                <a href="{{ route('companies.show', $company) }}" class="text-decoration-none">
                                    {{ $company->name }}
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $company->number_registre ?? '-' }}
                            </td>
                            <td>
                                @if($company->sector)
                                <span class="badge bg-secondary">{{ $company->sector }}</span>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $company->email ?? '-' }}
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $company->phone ?? '-' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-info">{{ $company->sites_count ?? 0 }}</span>
                            </td>
                            <td class="text-center">
                                @if($company->is_active)
                                <span class="badge bg-success">Actif</span>
                                @else
                                <span class="badge bg-secondary">Inactif</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('companies.show', $company) }}" class="btn btn-sm btn-outline-info">
                                    <i data-feather="eye" style="width:14px;height:14px;"></i>
                                </a>
                                <a href="{{ route('companies.edit', $company) }}" class="btn btn-sm btn-outline-warning">
                                    <i data-feather="edit-2" style="width:14px;height:14px;"></i>
                                </a>
                                <form method="POST" action="{{ route('companies.destroy', $company) }}" style="display:inline;"
                                      onsubmit="return confirm('Supprimer cette entreprise ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i data-feather="trash-2" style="width:14px;height:14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @if($companies->hasPages())
        <div class="card-footer">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    @if ($companies->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">← Précédent</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $companies->previousPageUrl() }}" rel="prev">← Précédent</a>
                        </li>
                    @endif

                    @for ($page = 1; $page <= $companies->lastPage(); $page++)
                        @if ($page == $companies->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $companies->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor

                    @if ($companies->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $companies->nextPageUrl() }}" rel="next">Suivant →</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">Suivant →</span>
                        </li>
                    @endif
                </ul>
            </nav>
        </div>
        @endif
    </div>

</x-layout.app>
