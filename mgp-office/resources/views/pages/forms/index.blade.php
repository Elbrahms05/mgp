<x-layout.app title="Formulaires ODK"
description="Formulaires XLSForm / ODK - Formulaires mobiles PACIPA MGP (ODK Collect)">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Formulaires ODK</li>
    </x-slot>

    <x-slot name="actions">
        @can('forms.manage')
        <a href="{{ route('forms.create') }}" class="btn btn-success btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;" class="me-1"></i> Associer un formulaire
        </a>
        @endcan
    </x-slot>

    {{-- Alerte si ODK Central inaccessible --}}
    @if($odkError)
    <div class="alert alert-warning py-2 mt-3">
        <i data-feather="wifi-off" style="width:14px;height:14px;" class="me-1"></i>
        ODK Central inaccessible - état en direct non disponible.
        <span class="text-muted small ms-2">{{ Str::limit($odkError, 100) }}</span>
    </div>
    @endif

    {{-- Formulaires enregistrés --}}
    <div class="card margin-row" style="border-radius:8px;">
        <div class="card-body">
            <h5 class="fw-semibold mb-3" style="font-size:1rem;">
                Formulaires enregistrés
                <span class="badge bg-secondary ms-2" style="font-size:0.75rem;">{{ $forms->count() }}</span>
            </h5>

            @if($forms->isEmpty())
            <div class="text-muted small py-2">
                <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                Aucun formulaire enregistré.
                @can('forms.manage')
                <a href="{{ route('forms.create') }}" style="color:#E05206;">Associer un formulaire ODK Central.</a>
                @endcan
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:0.85rem;">
                    <thead>
                        <tr>
                            <th>ID ODK</th>
                            <th>Nom</th>
                            <th>Version</th>
                            <th>État ODK</th>
                            <th>Soumissions</th>
                            <th>Actif</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($forms as $form)
                        @php $odkData = $odkForms[$form->form_id] ?? null; @endphp
                        <tr>
                            <td><code>{{ $form->form_id }}</code></td>
                            <td>{{ $form->name }}</td>
                            <td class="text-muted">
                                {{ $odkData ? ($odkData['version'] ?? '-') : ($form->current_version ?? '-') }}
                            </td>
                            <td>
                                @if($odkData)
                                @php
                                    $s = $odkData['state'] ?? 'open';
                                    $badge = ['open' => 'bg-success', 'closing' => 'bg-warning text-dark', 'closed' => 'bg-secondary'];
                                    $label = ['open' => 'Ouvert', 'closing' => 'En fermeture', 'closed' => 'Fermé'];
                                @endphp
                                <span class="badge {{ $badge[$s] ?? 'bg-secondary' }}" style="font-size:0.7rem;">
                                    {{ $label[$s] ?? $s }}
                                </span>
                                @else
                                <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-muted">
                                @if($odkData)
                                    {{ number_format($odkForms[$form->form_id]['submissionsCount'] ?? 0) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                @if($form->is_active)
                                <i data-feather="check-circle" style="width:16px;height:16px;color:#009A44;"></i>
                                @else
                                <i data-feather="x-circle" style="width:16px;height:16px;color:#6C757D;"></i>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('forms.show', $form) }}" class="btn btn-link btn-sm" title="Voir">
                                    <i data-feather="eye" style="width:16px;height:16px;"></i>
                                </a>
                                @can('forms.manage')
                                <a href="{{ route('forms.edit', $form) }}" class="btn btn-link btn-sm text-warning" title="Modifier">
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
    </div>

    {{-- Formulaires ODK Central non encore enregistrés --}}
    @if(!$odkError && !empty($odkForms))
    @php
        $registeredIds = $forms->pluck('form_id')->all();
        $unregistered  = collect($odkForms)->filter(fn($f) => !in_array($f['xmlFormId'], $registeredIds));
    @endphp
    @if($unregistered->isNotEmpty())
    <div class="card" style="border-radius:8px;">
        <div class="card-body">
            <h5 class="fw-semibold mb-3" style="font-size:1rem;">
                Disponibles sur ODK Central - non encore enregistrés
                <span class="badge bg-warning text-dark ms-2" style="font-size:0.75rem;">{{ $unregistered->count() }}</span>
            </h5>
            <div class="table-responsive">
                <table class="table table-hover" style="font-size:0.85rem;">
                    <thead>
                        <tr><th>ID ODK</th><th>Nom</th><th>Version</th><th>État</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($unregistered as $f)
                        @php
                            $s = $f['state'] ?? 'open';
                            $badge = ['open' => 'bg-success', 'closing' => 'bg-warning text-dark', 'closed' => 'bg-secondary'];
                            $label = ['open' => 'Ouvert', 'closing' => 'En fermeture', 'closed' => 'Fermé'];
                        @endphp
                        <tr>
                            <td><code>{{ $f['xmlFormId'] }}</code></td>
                            <td>{{ $f['name'] ?? '-' }}</td>
                            <td class="text-muted">{{ $f['version'] ?? '-' }}</td>
                            <td>
                                <span class="badge {{ $badge[$s] ?? 'bg-secondary' }}" style="font-size:0.7rem;">
                                    {{ $label[$s] ?? $s }}
                                </span>
                            </td>
                            <td class="text-end">
                                @can('forms.manage')
                                <a href="{{ route('forms.create') }}?form_id={{ urlencode($f['xmlFormId']) }}"
                                   class="btn btn-outline-success btn-sm" style="font-size:0.75rem;">
                                    <i data-feather="plus" style="width:12px;height:12px;" class="me-1"></i> Enregistrer
                                </a>
                                @endcan
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
    @endif

</x-layout.app>
