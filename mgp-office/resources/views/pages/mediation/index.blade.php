<x-layout.app title="Médiation"
    description="Sessions de médiation (Annexe 5 - PV de conciliation)"
    icon="users">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Médiation</li>
    </x-slot>
    <x-slot name="actions">
        @can('mediation.create')
        <a href="{{ route('mediation.create') }}" class="btn btn-success">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouvelle session
        </a>
        @endcan
    </x-slot>
    <div x-data="{ init() {} }" x-init="init()" class="margin-row">

       

        <div class="alert alert-warning" style="border-radius:8px;">
            <i data-feather="alert-triangle" style="width:16px;height:16px;" class="me-2"></i>
            <strong>Rappel :</strong>
            La médiation ne s'applique PAS aux plaintes VBG/EAS/HS (Type 8). Ces cas sont traités via le circuit VBG dédié.
        </div>

        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('mediation.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche plainte</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}"
                                    placeholder="N° plainte, objet..." class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="filter_accord" class="form-label">Accord</label>
                                <select name="is_agreement_reached" id="filter_accord" class="form-select">
                                    <option value="">Tous résultats</option>
                                    <option value="1" {{ request('is_agreement_reached') === '1' ? 'selected' : '' }}>Accord trouvé</option>
                                    <option value="0" {{ request('is_agreement_reached') === '0' ? 'selected' : '' }}>Sans accord</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-2 d-flex align-items-end pb-3 gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="search" style="width:16px;height:16px;"></i>
                            </button>
                            <a href="{{ route('mediation.index') }}" class="btn btn-outline-secondary">
                                <i data-feather="x" style="width:16px;height:16px;"></i>
                            </a>
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

        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($mediations->isEmpty())
                <div style="text-align:center;padding:48px;color:#9e9e9e;">
                    <i data-feather="users" style="width:48px;height:48px;"></i>
                    <p>Aucune session de médiation enregistrée.</p>
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="margin:0;font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Plainte</th>
                                <th>Type</th>
                                <th>Date session</th>
                                <th>Présidé par</th>
                                <th class="text-center">Accord</th>
                                <th class="text-end">Voir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mediations as $mediation)
                            <tr>
                                <td>
                                    <a href="{{ route('complaints.show', $mediation->complaint) }}" style="color:#E05206;">
                                        {{ $mediation->complaint?->registration_number }}
                                    </a>
                                </td>
                                <td class="text-muted">{{ $mediation->complaint?->complaintType?->label ?? '-' }}</td>
                                <td>{{ $mediation->session_date?->format('d/m/Y') }}</td>
                                <td>{{ $mediation->presided_by_name }} <small class="text-muted">({{ $mediation->presided_by_role }})</small></td>
                                <td class="text-center">
                                    @if($mediation->is_agreement_reached)
                                    <i data-feather="check-circle" style="width:16px;height:16px;color:#009A44;"></i>
                                    @else
                                    <i data-feather="x-circle" style="width:16px;height:16px;color:#DC3545;"></i>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('mediation.show', $mediation) }}" class="btn btn-link btn-sm">
                                        <i data-feather="eye" style="width:16px;height:16px;"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @if($mediations->hasPages())
            <div class="card-footer text-center">
                {{ $mediations->links() }}
            </div>
            @endif
        </div>

    </div>

</x-layout.app>