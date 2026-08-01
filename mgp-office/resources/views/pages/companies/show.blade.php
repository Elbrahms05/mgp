<x-layout.app
    title="{{ $company->name }}"
    description="Détails de l'entreprise et sites associés"
    icon="briefcase">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Entreprises</a></li>
        <li class="breadcrumb-item active">{{ $company->name }}</li>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('companies.edit', $company) }}" class="btn btn-warning btn-sm">
            <i data-feather="edit-2" style="width:16px;height:16px;"></i> Modifier
        </a>
        <form method="POST" action="{{ route('companies.destroy', $company) }}" style="display:inline;"
              onsubmit="return confirm('Supprimer cette entreprise ?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i data-feather="trash-2" style="width:16px;height:16px;"></i> Supprimer
            </button>
        </form>
    </x-slot>

    <div class="row margin-row">
        {{-- Détails entreprise --}}
        <div class="col-12 col-lg-4">
            <div class="card">
                <div class="card-header border-bottom">
                    <h6 class="fw-semibold mb-0">
                        <i data-feather="briefcase" class="me-2" style="width:14px;height:14px;"></i>
                        Informations
                    </h6>
                </div>
                <div class="card-body" style="padding:16px 24px;">
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Statut</span>
                        <div class="mt-1">
                            @if($company->is_active)
                            <span class="badge bg-success">Actif</span>
                            @else
                            <span class="badge bg-secondary">Inactif</span>
                            @endif
                        </div>
                    </div>

                    @if($company->sector)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Secteur</span>
                        <div class="mt-1 fw-semibold">{{ $company->sector }}</div>
                    </div>
                    @endif

                    @if($company->number_registre)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">N° RCCM</span>
                        <div class="mt-1 fw-semibold" style="font-family:monospace;">{{ $company->number_registre }}</div>
                    </div>
                    @endif

                    @if($company->email)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Email</span>
                        <div class="mt-1">
                            <a href="mailto:{{ $company->email }}" class="text-decoration-none">{{ $company->email }}</a>
                        </div>
                    </div>
                    @endif

                    @if($company->phone)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Téléphone</span>
                        <div class="mt-1">
                            <a href="tel:{{ $company->phone }}" class="text-decoration-none">{{ $company->phone }}</a>
                        </div>
                    </div>
                    @endif

                    @if($company->website)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Site web</span>
                        <div class="mt-1">
                            <a href="{{ $company->website }}" target="_blank" class="text-decoration-none" rel="noopener noreferrer">
                                {{ parse_url($company->website, PHP_URL_HOST) }}
                                <i data-feather="external-link" style="width:12px;height:12px;" class="ms-1"></i>
                            </a>
                        </div>
                    </div>
                    @endif

                    @if($company->address)
                    <div class="mb-3">
                        <span class="text-muted d-block" style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.5px;">Adresse</span>
                        <div class="mt-1" style="line-height:1.5;">{{ $company->address }}</div>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top text-muted" style="font-size:0.8rem;">
                        Créé {{ $company->created_at->diffForHumans() }}
                        @if($company->updated_at != $company->created_at)
                        <br>Modifié {{ $company->updated_at->diffForHumans() }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Sites associés --}}
        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="fw-semibold mb-0">
                            <i data-feather="map-pin" class="me-2" style="width:14px;height:14px;"></i>
                            Sites ({{ $company->sites_count ?? 0 }})
                        </h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#attachSiteModal">
                            <i data-feather="plus" style="width:14px;height:14px;"></i> Ajouter site
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($company->sites->isEmpty())
                    <div class="text-center text-muted" style="padding:48px;">
                        <i data-feather="map-pin" style="width:40px;height:40px;display:block;margin:0 auto 16px;"></i>
                        <p>Aucun site associé. Utilisez le bouton ci-dessus pour en ajouter.</p>
                    </div>
                    @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size:0.85rem;">
                            <thead>
                                <tr>
                                    <th>Nom du site</th>
                                    <th>Région</th>
                                    <th>Responsable</th>
                                    <th>Téléphone</th>
                                    <th>Email</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($company->sites as $site)
                                <tr>
                                    <td class="fw-semibold">
                                        <a href="{{ route('sites.show', $site) }}" class="text-decoration-none">
                                            {{ $site->name }}
                                        </a>
                                    </td>
                                    <td class="text-muted">{{ $site->locality->commune->department->region->name ?? '-' }}</td>
                                    <td>{{ $site->pivot->responsible_name ?? '-' }}</td>
                                    <td class="text-muted" style="font-size:0.8rem;">{{ $site->pivot->responsible_phone ?? '-' }}</td>
                                    <td class="text-muted" style="font-size:0.8rem;">{{ $site->pivot->responsible_email ?? '-' }}</td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-info"
                                                onclick="editResponsible({{ $site->id }}, '{{ $site->pivot->responsible_name }}', '{{ $site->pivot->responsible_phone }}', '{{ $site->pivot->responsible_email }}')">
                                            <i data-feather="edit-2" style="width:14px;height:14px;"></i>
                                        </button>
                                        <form method="POST" action="{{ route('companies.sites.detach', [$company, $site]) }}" 
                                              style="display:inline;"
                                              onsubmit="return confirm('Détacher ce site ?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                                <i data-feather="x" style="width:14px;height:14px;"></i>
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
            </div>
        </div>
    </div>

    {{-- Modal: Ajouter site --}}
    <div class="modal fade" id="attachSiteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">Associer un site</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="{{ route('companies.sites.attach', $company) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="site_id" class="form-label">Site *</label>
                            <select name="site_id" id="site_id" class="form-select" required>
                                <option value="">- Sélectionner un site -</option>
                                @foreach($availableSites as $site)
                                <option value="{{ $site->id }}">
                                    {{ $site->name }} ({{ $site->locality->commune->department->region->name ?? '-' }})
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="responsible_name" class="form-label">Nom du responsable *</label>
                            <input type="text" name="responsible_name" id="responsible_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="responsible_phone" class="form-label">Téléphone</label>
                            <input type="tel" name="responsible_phone" id="responsible_phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="responsible_email" class="form-label">Email</label>
                            <input type="email" name="responsible_email" id="responsible_email" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-success">Ajouter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal: Modifier responsable --}}
    <div class="modal fade" id="editResponsibleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title">Modifier responsable</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editResponsibleForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_responsible_name" class="form-label">Nom du responsable *</label>
                            <input type="text" name="responsible_name" id="edit_responsible_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_responsible_phone" class="form-label">Téléphone</label>
                            <input type="tel" name="responsible_phone" id="edit_responsible_phone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="edit_responsible_email" class="form-label">Email</label>
                            <input type="email" name="responsible_email" id="edit_responsible_email" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function editResponsible(siteId, name, phone, email) {
            document.getElementById('editResponsibleForm').action = 
                `{{ route('companies.sites.responsible.update', [$company, ':siteId']) }}`.replace(':siteId', siteId);
            document.getElementById('edit_responsible_name').value = name;
            document.getElementById('edit_responsible_phone').value = phone;
            document.getElementById('edit_responsible_email').value = email;
            
            const modal = new bootstrap.Modal(document.getElementById('editResponsibleModal'));
            modal.show();
        }
    </script>

</x-layout.app>
