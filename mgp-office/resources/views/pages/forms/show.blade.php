<x-layout.app
    title="{{ $form->name }}"
    description="{{ $formRefs[$form->form_id] ?? 'Formulaire ODK PACIPA-MGP' }}"
    icon="file-text">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('forms.index') }}">Formulaires</a></li>
        <li class="breadcrumb-item active">{{ $form->form_id }}</li>
    </x-slot>

    @can('forms.manage')
    <x-slot name="actions">
        <a href="{{ route('forms.edit', $form) }}" class="btn btn-sm btn-outline-light">
            <i data-feather="edit-2" style="width:14px;height:14px;" class="me-1"></i>Modifier
        </a>
    </x-slot>
    @endcan

    @php
        $stateLabel = ['open' => 'Ouvert', 'closing' => 'En fermeture', 'closed' => 'Fermé'];
        $stateBadge = ['open' => 'bg-success', 'closing' => 'bg-warning text-dark', 'closed' => 'bg-secondary'];
    @endphp

    {{-- Alerte si ODK inaccessible --}}
    @if($odkError)
    <div class="alert alert-warning py-2 mt-3">
        <i data-feather="wifi-off" style="width:14px;height:14px;" class="me-1"></i>
        ODK Central inaccessible - données locales uniquement.
        <span class="text-muted small ms-2">{{ Str::limit($odkError, 80) }}</span>
    </div>
    @endif

    <div class="row g-4 mt-n2">

        {{-- ══════════════════════════ COLONNE PRINCIPALE ══════════════════ --}}
        <div class="col-lg-8">

            {{-- Informations générales ───────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3">
                    <span class="fw-semibold" style="color: white;">
                        <i data-feather="file-text" style="width:16px;height:16px;" class="me-2"></i>
                        Informations générales
                    </span>
                    @if($odkDetails)
                    @php $state = $odkDetails['state'] ?? 'open'; @endphp
                    <span class="badge {{ $stateBadge[$state] ?? 'bg-secondary' }}">
                        {{ $stateLabel[$state] ?? $state }}
                    </span>
                    @elseif($form->is_active)
                    <span class="badge bg-success">Actif</span>
                    @else
                    <span class="badge bg-secondary">Inactif</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">ID formulaire ODK</div>
                            <div class="fw-semibold"><code>{{ $form->form_id }}</code></div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Référence MGP</div>
                            <div class="fw-semibold">{{ $formRefs[$form->form_id] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Version courante (locale)</div>
                            <div class="fw-semibold">v{{ $form->current_version ?? '-' }}</div>
                        </div>
                        @if($odkDetails)
                        <div class="col-md-6">
                            <div class="small text-muted">Version ODK Central</div>
                            <div class="fw-semibold">{{ $odkDetails['version'] ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Soumissions totales</div>
                            <div class="fw-semibold">
                                <i data-feather="inbox" style="width:14px;height:14px;" class="me-1 text-primary"></i>
                                {{ number_format($submissionsCount ?? 0) }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Publié le (ODK)</div>
                            <div class="fw-semibold">
                                {{ isset($odkDetails['publishedAt'])
                                    ? \Carbon\Carbon::parse($odkDetails['publishedAt'])->format('d/m/Y H:i')
                                    : '-' }}
                            </div>
                        </div>
                        @if(!empty($odkDetails['sha256']))
                        <div class="col-12">
                            <div class="small text-muted">Hash SHA-256 (XForm)</div>
                            <div><code class="small text-muted">{{ $odkDetails['sha256'] }}</code></div>
                        </div>
                        @endif
                        @if(!empty($odkDetails['enketoId']))
                        <div class="col-12">
                            <div class="small text-muted">Enketo (aperçu web)</div>
                            <div class="small">
                                <i data-feather="external-link" style="width:12px;height:12px;" class="me-1"></i>
                                <code>{{ $odkDetails['enketoId'] }}</code>
                            </div>
                        </div>
                        @endif
                        @endif
                        @if($form->description)
                        <div class="col-12">
                            <div class="small text-muted">Description</div>
                            <div>{{ $form->description }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Versions ODK Central (live) ─────────────────────────────── --}}
            @if(count($odkVersions) > 0)
            <div class="card mb-4" >
                <div class="card-header py-3 d-flex align-items-center justify-content-between" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="cloud" style="width:16px;height:16px;" class="me-2"></i>
                        Versions ODK Central
                    </span>
                    <span class="badge bg-primary rounded-pill">{{ count($odkVersions) }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0" style="font-size:0.85rem;">
                            <thead class="table-light">
                                <tr>
                                    <th>Version</th>
                                    <th>État</th>
                                    <th>Publiée le</th>
                                    <th>SHA-256</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach(collect($odkVersions)->sortByDesc('publishedAt') as $v)
                                @php $vState = $v['state'] ?? 'open'; @endphp
                                <tr>
                                    <td class="fw-semibold">
                                        {{ $v['version'] ?? '-' }}
                                        @if(($v['version'] ?? '') === ($odkDetails['version'] ?? ''))
                                        <span class="badge bg-primary ms-1" style="font-size:0.65rem;">COURANTE</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $stateBadge[$vState] ?? 'bg-secondary' }}" style="font-size:0.7rem;">
                                            {{ $stateLabel[$vState] ?? $vState }}
                                        </span>
                                    </td>
                                    <td class="text-muted">
                                        {{ isset($v['publishedAt'])
                                            ? \Carbon\Carbon::parse($v['publishedAt'])->format('d/m/Y H:i')
                                            : '-' }}
                                    </td>
                                    <td>
                                        @if(!empty($v['sha256']))
                                        <code class="text-muted" style="font-size:0.7rem;" title="{{ $v['sha256'] }}">
                                            {{ substr($v['sha256'], 0, 12) }}…
                                        </code>
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @elseif(!$odkError)
            <div class="alert alert-info">
                <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                Aucune version publiée sur ODK Central pour <code>{{ $form->form_id }}</code>.
            </div>
            @endif

        </div>

        {{-- ══════════════════════════ COLONNE LATÉRALE ═══════════════════ --}}
        <div class="col-lg-4">

            {{-- Résumé ODK en direct ─────────────────────────────────────── --}}
            @if($odkDetails)
            <div class="card mb-4">
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="activity" style="width:16px;height:16px;" class="me-2"></i>
                        ODK Central - en direct
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="small text-muted">Soumissions</div>
                            <div class="fw-bold fs-5">{{ number_format($submissionsCount ?? 0) }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Version</div>
                            <div class="fw-bold fs-5">{{ $odkDetails['version'] ?? '-' }}</div>
                        </div>
                    </div>
                    @if(!empty($odkDetails['name']))
                    <hr class="my-2">
                    <div class="small text-muted">Nom sur ODK Central</div>
                    <div class="fw-semibold small">{{ $odkDetails['name'] }}</div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</x-layout.app>
