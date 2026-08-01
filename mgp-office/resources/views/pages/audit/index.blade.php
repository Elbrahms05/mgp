<x-layout.app
    title="Journal d'audit"
    description="Traçabilité complète - toutes les actions sont enregistrées (Spatie Activity Log)"
    icon="clock">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Journal d'audit</li>
    </x-slot>

    {{-- Filtres --}}
    <div class="card margin-row">
        <div class="card-body" style="padding:16px 24px 8px;">
            <form method="GET" action="{{ route('audit.index') }}">
                <div class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label for="search" class="form-label">Recherche</label>
                        <input type="text" id="search" name="search" class="form-control"
                               value="{{ request('search') }}" placeholder="Description, log...">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="filter_event" class="form-label">Événement</label>
                        <select name="event" id="filter_event" class="form-select">
                            <option value="">Tous événements</option>
                            @foreach($events as $evt)
                            <option value="{{ $evt }}" {{ request('event') === $evt ? 'selected' : '' }}>
                                {{ ucfirst($evt) }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <label for="filter_user" class="form-label">Utilisateur</label>
                        <select name="causer_id" id="filter_user" class="form-select">
                            <option value="">Tous les utilisateurs</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ request('causer_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="date_from" class="form-label">Du</label>
                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-6 col-md-2">
                        <label for="date_to" class="form-label">Au</label>
                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-12 col-md-auto">
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="search" style="width:15px;height:15px;" class="me-1"></i>Filtrer
                        </button>
                        <a href="{{ route('audit.index') }}" class="btn btn-outline-secondary ms-1">
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
            @if($activities->isEmpty())
            <div class="text-center text-muted" style="padding:48px;">
                <i data-feather="clock" style="width:48px;height:48px;display:block;margin:0 auto 16px;"></i>
                <p>Aucune activité trouvée pour ces critères.</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;font-size:0.83rem;">
                    <thead>
                        <tr>
                            <th>Date / Heure</th>
                            <th>Utilisateur</th>
                            <th>Événement</th>
                            <th>Objet modifié</th>
                            <th>ID</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($activities as $activity)
                        <tr>
                            <td class="text-muted" style="white-space:nowrap;">
                                {{ $activity->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td>
                                @if($activity->causer)
                                <span style="white-space:nowrap;">
                                    <i data-feather="user" style="width:14px;height:14px;" class="me-1"></i>{{ $activity->causer->name ?? '-' }}
                                </span>
                                @else
                                <span class="text-muted">Système</span>
                                @endif
                            </td>
                            <td>
                                @php
                                $eventBadge = match($activity->event) {
                                    'created'  => 'success',
                                    'updated'  => 'primary',
                                    'deleted'  => 'danger',
                                    'restored' => 'warning',
                                    default    => 'secondary',
                                };
                                @endphp
                                <span class="badge bg-{{ $eventBadge }}">{{ $activity->event ?? '-' }}</span>
                            </td>
                            <td class="text-muted">
                                {{ class_basename($activity->subject_type ?? '') ?: '-' }}
                            </td>
                            <td class="text-muted">{{ $activity->subject_id ?? '-' }}</td>
                            <td style="max-width:300px;">
                                {{ Str::limit($activity->description ?? '', 80) }}
                                @if($activity->properties && $activity->properties->count() > 0)
                                <i data-feather="info" style="width:14px;height:14px;" class="ms-1 text-muted"
                                   title="{{ json_encode($activity->properties->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}"
                                   style="cursor:help;"></i>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @if($activities->hasPages())
        <div class="card-footer">
            <nav aria-label="Page navigation" class="d-flex justify-content-center">
                <ul class="pagination mb-0">
                    {{-- Previous Page Link --}}
                    @if ($activities->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true">
                            <span class="page-link">← Précédent</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $activities->previousPageUrl() }}" rel="prev">← Précédent</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @for ($page = 1; $page <= $activities->lastPage(); $page++)
                        @if ($page == $activities->currentPage())
                            <li class="page-item active" aria-current="page">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $activities->url($page) }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endfor

                    {{-- Next Page Link --}}
                    @if ($activities->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $activities->nextPageUrl() }}" rel="next">Suivant →</a>
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
