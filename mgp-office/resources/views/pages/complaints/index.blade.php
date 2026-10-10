<x-layout.app title="Plaintes"
    description="Plaintes enregistrées, suivi et gestion"
    icon="file-text">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Plaintes</li>
    </x-slot>
    <x-slot name="actions">
        @can('complaints.create')
        <a href="{{ route('complaints.create') }}" class="btn btn-success">
            <i data-feather="plus" class="me-1" style="width:15px;height:15px;"></i>Nouvelle plainte
        </a>
        <a href="{{ route('complaints.deferred.create') }}" class="btn btn-outline-secondary ms-1"
           title="Reprise d'une fiche papier reçue au niveau communal ou régional">
            <i data-feather="file-plus" class="me-1" style="width:15px;height:15px;"></i>Saisie différée (papier)
        </a>
        @endcan
        @can('complaints.export')
        <a href="{{ route('complaints.export') }}" class="btn btn-warning ms-1">
            <i data-feather="download" class="me-1" style="width:15px;height:15px;"></i>Export CSV
        </a>
        @endcan
    </x-slot>
    <div x-data="complaintIndex()" x-init="init()" style="margin-top:24px;margin-bottom:16px;">

        {{-- Filtres --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body">
                <form method="GET" action="{{ route('complaints.index') }}">
                    <div class="row g-2 align-items-end">
                        <div class="col-12 col-md-3">
                            <label for="search" class="form-label">Recherche</label>
                            <input type="text" id="search" name="search"
                                value="{{ request('search') }}"
                                placeholder="N° plainte, objet, plaignant..."
                                class="form-control">
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="filter_status" class="form-label">Statut</label>
                            <select name="status" id="filter_status" class="form-select">
                                <option value="">Tous statuts</option>
                                @foreach(\App\Domains\Complaint\Enums\ComplaintStatus::cases() as $s)
                                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>
                                    {{ $s->label() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="filter_type" class="form-label">Type</label>
                            <select name="complaint_type_id" id="filter_type" class="form-select">
                                <option value="">Tous types</option>
                                @foreach($complaintTypes as $ct)
                                <option value="{{ $ct->id }}" {{ request('complaint_type_id') == $ct->id ? 'selected' : '' }}>
                                    {{ $ct->label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2">
                            <label for="filter_channel" class="form-label">Canal</label>
                            <select name="channel" id="filter_channel" class="form-select">
                                <option value="">Tous canaux</option>
                                @foreach($channels as $ch)
                                <option value="{{ $ch->value }}" {{ request('channel') === $ch->value ? 'selected' : '' }}>
                                    {{ $ch->label() }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="sla_breach" value="1"
                                    id="sla_breach_chk"
                                    {{ request('sla_breach') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label text-danger small" for="sla_breach_chk">SLA dépassé</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-1">
                            <button type="submit" class="btn btn-primary w-100">
                                <i data-feather="search" style="width:15px;height:15px;"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        {{-- Tableau --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($complaints->isEmpty())
                <div class="text-center p-5 text-muted">
                    <i data-feather="inbox" style="width:48px;height:48px;"></i>
                    <p class="mt-2">Aucune plainte trouvée.</p>
                    @can('complaints.create')
                    <a href="{{ route('complaints.create') }}" class="btn btn-success">Enregistrer une plainte</a>
                    <a href="{{ route('complaints.deferred.create') }}" class="btn btn-outline-secondary ms-1">Saisie différée (papier)</a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle" style="margin:0;">
                        <thead>
                            <tr>
                                <th>N° Plainte</th>
                                <th>Type</th>
                                <th>Objet</th>
                                <th>Site</th>
                                <th class="text-center">Statut</th>
                                <th>SLA</th>
                                <th>Canal</th>
                                <th>Date</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($complaints as $complaint)
                            @php $hoursRemaining = app(\App\Domains\Complaint\Services\SlaMonitorService::class)->hoursRemaining($complaint); @endphp
                            <tr class="{{ app(\App\Domains\Complaint\Services\SlaMonitorService::class)->isBreached($complaint) ? 'table-danger' : '' }}">
                                <td>
                                    <a href="{{ route('complaints.show', $complaint) }}" class="text-primary">
                                        <code style="font-size:0.8rem;">{{ $complaint->registration_number ?? '#'.$complaint->id }}</code>
                                    </a>
                                </td>
                                <td>
                                    <x-complaint.type-icon :type="$complaint->complaintType" />
                                    <span style="font-size:0.8rem;">{{ $complaint->complaintType?->label ?? '-' }}</span>
                                </td>
                                <td style="max-width:200px;">
                                    <span class="d-block text-truncate" style="max-width:200px;">
                                        {{ $complaint->is_anonymous ? '[Anonyme]' : $complaint->complainant_name }}
                                        - {{ Str::limit($complaint->subject, 50) }}
                                    </span>
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $complaint->site?->name ?? 'Hors site' }}
                                </td>
                                <td class="text-center">
                                    <x-complaint.status-badge :status="$complaint->status" />
                                </td>
                                <td>
                                    <x-complaint.sla-countdown
                                        :complaint="$complaint"
                                        :hoursRemaining="$hoursRemaining"
                                        :isBreached="$hoursRemaining < 0" />
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $complaint->channel?->label() ?? '-' }}
                                </td>
                                <td class="text-muted" style="font-size:0.8rem;">
                                    {{ $complaint->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end">
                                    <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-link btn-sm text-primary p-1">
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
            @if($complaints->hasPages())
            <div class="card-footer text-center">
                {{ $complaints->links() }}
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
        function complaintIndex() {
            return {
                init() {
                    // Native Bootstrap selects - no JS init needed
                }
            };
        }
    </script>
    @endpush

</x-layout.app>