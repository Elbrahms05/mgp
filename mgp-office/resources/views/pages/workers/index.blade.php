<x-layout.app 
    title="Plaintes travailleurs" 
    description="Travailleurs directs UGP + travailleurs contractuels (Manuel MGP §4.4)"
    icon="briefcase">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Plaintes travailleurs</li>
    </x-slot>

    <x-slot name="actions">
        @can('workers.manage')
        <a href="{{ route('workers.create') }}" class="btn btn-success btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouvelle plainte
        </a>
        @endcan
    </x-slot>

    <div x-data="{ init() {} }" x-init="init()" style="margin-top:24px;margin-bottom:16px;">

        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('workers.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" id="search" name="search" value="{{ request('search') }}"
                                       placeholder="N°, travailleur, employeur..." class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="filter_type" class="form-label">Type</label>
                                <select name="worker_type" id="filter_type" class="form-select">
                                    <option value="">Tous les types</option>
                                    <option value="direct" {{ request('worker_type') === 'direct' ? 'selected' : '' }}>Travailleurs directs</option>
                                    <option value="contractor" {{ request('worker_type') === 'contractor' ? 'selected' : '' }}>Travailleurs contractuels</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="filter_status" class="form-label">Statut</label>
                                <select name="status" id="filter_status" class="form-select">
                                    <option value="">Tous statuts</option>
                                    @foreach(['new' => 'Nouveau', 'under_review' => 'En examen', 'escalated' => 'Escaladé', 'resolved' => 'Résolu', 'closed' => 'Clôturé'] as $val => $label)
                                    <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end pb-3 gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="search" style="width:16px;height:16px;"></i>
                            </button>
                            <a href="{{ route('workers.index') }}" class="btn btn-outline-secondary">
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
                @if($workers->isEmpty())
                <div style="text-align:center;padding:48px;color:#9e9e9e;">
                    <i data-feather="tool" style="width:48px;height:48px;"></i>
                    <p>Aucune plainte travailleur enregistrée.</p>
                    @can('workers.manage')
                    <a href="{{ route('workers.create') }}" class="btn btn-success">Enregistrer une plainte</a>
                    @endcan
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>N° Plainte</th>
                            <th>Type</th>
                            <th>Travailleur</th>
                            <th>Employeur</th>
                            <th>Objet</th>
                            <th>Niveau</th>
                            <th class="text-center">Statut</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($workers as $worker)
                        <tr>
                            <td>
                                <a href="{{ route('workers.show', $worker) }}" style="color:#E05206;">
                                    <code style="font-size:0.8rem;">{{ $worker->registration_number }}</code>
                                </a>
                            </td>
                            <td>
                                @if($worker->worker_type === 'direct')
                                <span style="background:#e3f2fd;color:#E05206;border-radius:4px;font-size:0.7rem;padding:2px 6px;font-weight:600;">Direct UGP</span>
                                @else
                                <span style="background:#fff3e0;color:#F97B3D;border-radius:4px;font-size:0.7rem;padding:2px 6px;font-weight:600;">Contractuel</span>
                                @endif
                            </td>
                            <td>{{ $worker->worker_name }}</td>
                            <td class="text-muted">
                                @if($worker->worker_type === 'contractor' && $worker->company)
                                    {{ $worker->company->name }}
                                @else
                                    {{ $worker->employer_name }}
                                @endif
                            </td>
                            <td style="max-width:200px;">
                                <span style="display:block;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ Str::limit($worker->subject, 50) }}</span>
                            </td>
                            <td class="text-center">
                                <span style="background:#f5f5f5;color:#6C757D;border-radius:4px;font-size:0.7rem;padding:2px 6px;font-weight:600;">N{{ $worker->level }}</span>
                            </td>
                            <td class="text-center">
                                @php
                                $statusStyles = [
                                    'new'          => ['bg' => '#e3f2fd', 'color' => '#E05206'],
                                    'under_review' => ['bg' => '#fff3e0', 'color' => '#F97B3D'],
                                    'escalated'    => ['bg' => '#fbe9e7', 'color' => '#E05206'],
                                    'resolved'     => ['bg' => '#e8f5e9', 'color' => '#009A44'],
                                    'closed'       => ['bg' => '#f5f5f5', 'color' => '#6C757D'],
                                ];
                                $statusLabels = ['new' => 'Nouveau', 'under_review' => 'En examen', 'escalated' => 'Escaladé', 'resolved' => 'Résolu', 'closed' => 'Clôturé'];
                                $ss = $statusStyles[$worker->status->value] ?? ['bg' => '#f5f5f5', 'color' => '#6C757D'];
                                @endphp
                                <span style="background:{{ $ss['bg'] }};color:{{ $ss['color'] }};border-radius:12px;padding:2px 10px;font-size:0.75rem;font-weight:600;">
                                    {{ $worker->status->label() }}
                                </span>
                            </td>
                            <td class="text-muted">{{ $worker->created_at->format('d/m/Y') }}</td>
                            <td class="text-end">
                                <a href="{{ route('workers.show', $worker) }}" class="btn btn-link btn-sm" title="Voir">
                                    <i data-feather="eye" style="width:16px;height:16px;"></i>
                                </a>
                                @can('workers.manage')
                                <a href="{{ route('workers.edit', $worker) }}" class="btn btn-link btn-sm text-warning" title="Modifier">
                                    <i data-feather="edit-2" style="width:16px;height:16px;"></i>
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
            @if($workers->hasPages())
            <div class="card-footer text-center">
                {{ $workers->links() }}
            </div>
            @endif
        </div>

    </div>

</x-layout.app>
