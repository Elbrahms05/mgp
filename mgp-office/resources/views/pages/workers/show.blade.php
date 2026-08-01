<x-layout.app
    title="{{ $worker->registration_number }}"
    description="Plainte travailleur - {{ $worker->worker_type === 'direct' ? 'Agent direct UGP' : 'Contractuel' }}"
    icon="briefcase">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Plaintes travailleurs</a></li>
        <li class="breadcrumb-item active">{{ $worker->registration_number }}</li>
    </x-slot>

    @can('workers.manage')
    <x-slot name="actions">
        <a href="{{ route('workers.edit', $worker) }}" class="btn btn-sm btn-outline-light">
            <i data-feather="edit-2" style="width:14px;height:14px;" class="me-1"></i>Modifier
        </a>
    </x-slot>
    @endcan

    @php
        $allowedNext = $worker->status->allowedTransitions();

        $actionMeta = [
            'start_review'  => ['label' => "Démarrer l'examen", 'icon' => 'play-circle',    'btn' => 'btn-primary'],
            'escalate'      => ['label' => 'Escalader N2',       'icon' => 'arrow-up-circle','btn' => 'btn-warning'],
            'resolve'       => ['label' => 'Marquer résolu',     'icon' => 'check-circle',   'btn' => 'btn-success'],
            'close'         => ['label' => 'Clôturer',           'icon' => 'x-circle',       'btn' => 'btn-secondary'],
        ];

        // enum value → action key (for transition form)
        $statusToAction = [
            'under_review' => 'start_review',
            'escalated'    => 'escalate',
            'resolved'     => 'resolve',
            'closed'       => 'close',
        ];

        $historyIcons = [
            'start_review'  => ['icon' => 'play-circle',    'color' => '#1565c0'],
            'escalate'      => ['icon' => 'arrow-up-circle','color' => '#e65100'],
            'auto_escalate' => ['icon' => 'alert-circle',   'color' => '#c62828'],
            'resolve'       => ['icon' => 'check-circle',   'color' => '#2e7d32'],
            'close'         => ['icon' => 'x-circle',       'color' => '#6c757d'],
        ];

        $slaRatio    = $worker->sla_deadline ? min(1, $worker->slaRatio()) : null;
        $slaDaysLeft = $worker->sla_deadline ? $worker->slaDaysLeft()      : null;
        $barColor    = $slaRatio >= 1 ? 'bg-danger' : ($slaRatio >= 0.75 ? 'bg-warning' : 'bg-success');
    @endphp

    <div class="row g-4 mt-n2">

        {{-- ═══════════════════════════ COLONNE PRINCIPALE (8/12) ══════════ --}}
        <div class="col-lg-8">

            {{-- Statut + SLA ─────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header d-flex align-items-center justify-content-between py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="activity" style="width:16px;height:16px;" class="me-2"></i>
                        Statut &amp; SLA
                    </span>
                    <span class="badge {{ $worker->status->badgeClass() }} rounded-pill px-3">
                        {{ $worker->status->label() }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6 col-md-3 text-center">
                            <div class="small text-muted mb-1">Niveau</div>
                            <span class="badge bg-primary fs-6 px-3 py-2">N{{ $worker->level }}</span>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="small text-muted mb-1">Type</div>
                            <span class="fw-semibold small">
                                {{ $worker->worker_type === 'direct' ? 'Direct UGP' : 'Contractuel' }}
                            </span>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="small text-muted mb-1">Date du fait</div>
                            <span class="fw-semibold small">{{ $worker->incident_date?->format('d/m/Y') ?? '-' }}</span>
                        </div>
                        <div class="col-6 col-md-3 text-center">
                            <div class="small text-muted mb-1">Enregistré le</div>
                            <span class="fw-semibold small">{{ $worker->created_at->format('d/m/Y') }}</span>
                        </div>
                    </div>

                    @if($slaRatio !== null)
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <small class="text-muted">
                                <i data-feather="clock" style="width:12px;height:12px;" class="me-1"></i>
                                Échéance SLA : {{ $worker->sla_deadline->format('d/m/Y') }}
                            </small>
                            @if($worker->isSlaBreached())
                                <span class="badge bg-danger">SLA DÉPASSÉ</span>
                            @else
                                <span class="badge {{ $slaRatio >= 0.75 ? 'bg-warning text-dark' : 'bg-success' }}">
                                    {{ $slaDaysLeft }}j restant{{ $slaDaysLeft > 1 ? 's' : '' }}
                                </span>
                            @endif
                        </div>
                        <div class="progress" style="height:8px;">
                            <div class="progress-bar {{ $barColor }}"
                                 style="width:{{ round($slaRatio * 100) }}%;"
                                 role="progressbar"></div>
                        </div>
                    </div>
                    @elseif($worker->status->value === 'new')
                    <div class="alert alert-info py-2 mb-0">
                        <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                        Le délai SLA démarrera à la prise en charge de la plainte.
                    </div>
                    @endif
                </div>
            </div>

            {{-- Travailleur ───────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="user" style="width:16px;height:16px;" class="me-2"></i>
                        Travailleur
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="small text-muted">Nom complet</div>
                            <div class="fw-semibold">{{ $worker->worker_name }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Téléphone</div>
                            <div class="fw-semibold">{{ $worker->worker_phone ?? '-' }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Employeur</div>
                            <div class="fw-semibold">
                                @if($worker->worker_type === 'contractor' && $worker->company)
                                    {{ $worker->company->name }}
                                    @if($worker->company->sector)
                                        <span class="text-muted small">({{ $worker->company->sector }})</span>
                                    @endif
                                @else
                                    {{ $worker->employer_name ?? '-' }}
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="small text-muted">Enregistré par</div>
                            <div class="fw-semibold">{{ $worker->registeredBy?->name ?? '-' }}</div>
                        </div>
                        @if($worker->escalated_at)
                        <div class="col-md-6">
                            <div class="small text-muted">Escaladé le</div>
                            <div class="fw-semibold text-danger">{{ $worker->escalated_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @endif
                        @if($worker->resolved_at)
                        <div class="col-md-6">
                            <div class="small text-muted">Résolu le</div>
                            <div class="fw-semibold text-success">{{ $worker->resolved_at->format('d/m/Y H:i') }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description ──────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="file-text" style="width:16px;height:16px;" class="me-2"></i>
                        Description de la plainte
                    </span>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <span class="small text-muted">Objet</span>
                        <div class="fw-semibold">{{ $worker->subject }}</div>
                    </div>
                    <hr class="my-2">
                    <p class="mb-0" style="white-space:pre-wrap;font-size:0.9rem;line-height:1.65;">{{ $worker->description }}</p>
                </div>
            </div>

            {{-- Historique ───────────────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="clock" style="width:16px;height:16px;" class="me-2"></i>
                        Historique des actions
                    </span>
                </div>
                <div class="card-body p-0">
                    @forelse($worker->histories as $h)
                    @php $hi = $historyIcons[$h->action] ?? ['icon' => 'circle', 'color' => '#6c757d']; @endphp
                    <div class="d-flex gap-3 px-4 py-3 border-bottom">
                        <div class="flex-shrink-0 mt-1">
                            <i data-feather="{{ $hi['icon'] }}" style="width:18px;height:18px;color:{{ $hi['color'] }};"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <span class="fw-semibold small">
                                    {{ $h->from_status?->label() ?? '?' }}
                                    →
                                    {{ $h->to_status?->label() ?? '?' }}
                                </span>
                                @if($h->action === 'auto_escalate')
                                    <span class="badge bg-warning text-dark" style="font-size:0.65rem;">AUTO</span>
                                @endif
                                <span class="text-muted small ms-auto">{{ $h->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                            @if($h->comment)
                            <p class="mb-1 small fst-italic text-muted">"{{ $h->comment }}"</p>
                            @endif
                            <div class="small text-muted">
                                <i data-feather="user" style="width:11px;height:11px;" class="me-1"></i>
                                {{ $h->performedBy?->name ?? 'Système' }}
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="px-4 py-3 text-muted small">
                        <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                        Aucune action enregistrée pour le moment.
                    </div>
                    @endforelse
                </div>
            </div>

        </div>

        {{-- ═══════════════════════════ COLONNE LATÉRALE (4/12) ══════════ --}}
        <div class="col-lg-4">

            {{-- Boutons d'action (Alpine.js) ─────────────────────────────── --}}
            @can('workers.manage')
            @if(count($allowedNext) > 0)
            <div class="card mb-4"
                 x-data="{ activeAction: null, comment: '' }" >
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="zap" style="width:16px;height:16px;" class="me-2"></i>
                        Actions disponibles
                    </span>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @foreach($allowedNext as $next)
                        @php
                            $key  = $statusToAction[$next->value] ?? null;
                            $meta = $key ? $actionMeta[$key] : null;
                        @endphp
                        @if($key && $meta)
                        <button type="button"
                                class="btn {{ $meta['btn'] }} btn-sm"
                                @click="activeAction = '{{ $key }}'; comment = ''">
                            <i data-feather="{{ $meta['icon'] }}" style="width:14px;height:14px;" class="me-1"></i>
                            {{ $meta['label'] }}
                        </button>
                        @endif
                        @endforeach
                    </div>

                    <div x-show="activeAction !== null" x-cloak class="mt-3 border-top pt-3">
                        <form method="POST" action="{{ route('workers.transition', $worker) }}">
                            @csrf
                            <input type="hidden" name="action" x-bind:value="activeAction">
                            <div class="mb-3">
                                <label class="form-label small fw-semibold">
                                    Commentaire
                                    <span class="text-muted fw-normal">(facultatif)</span>
                                </label>
                                <textarea name="comment"
                                          class="form-control form-control-sm"
                                          rows="3" maxlength="1000"
                                          placeholder="Ajouter un commentaire…"
                                          x-model="comment"></textarea>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-dark btn-sm flex-grow-1">
                                    <i data-feather="check" style="width:14px;height:14px;" class="me-1"></i>
                                    Confirmer
                                </button>
                                <button type="button" class="btn btn-light btn-sm"
                                        @click="activeAction = null">Annuler</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            @endcan

            {{-- Circuit de traitement ─────────────────────────────────────── --}}
            <div class="card mb-4">
                <div class="card-header py-3" style="color: white;">
                    <span class="fw-semibold">
                        <i data-feather="git-branch" style="width:16px;height:16px;" class="me-2"></i>
                        Circuit de traitement
                    </span>
                </div>
                <div class="card-body p-0">
                    {{-- N1 --}}
                    <div class="px-4 py-3 d-flex align-items-start gap-3
                        {{ $worker->level === 1 && !$worker->status->isTerminal() ? 'bg-primary bg-opacity-10 border-start border-primary border-3' : '' }}">
                        <span class="badge mt-1 {{ $worker->level === 1 && !$worker->status->isTerminal() ? 'bg-primary' : 'bg-light text-muted border' }}"
                              style="min-width:2rem;">N1</span>
                        <div>
                            @if($worker->worker_type === 'direct')
                            <div class="fw-semibold small">Resp. admin UGP → Coordonnateur</div>
                            <div class="text-muted small">7 jours calendaires</div>
                            @else
                            <div class="fw-semibold small">Chef personnel → Directeur travaux</div>
                            <div class="text-muted small">7 jours ouvrables</div>
                            @endif
                        </div>
                    </div>
                    <hr class="my-0">
                    {{-- N2 --}}
                    <div class="px-4 py-3 d-flex align-items-start gap-3
                        {{ $worker->level === 2 && !$worker->status->isTerminal() ? 'bg-danger bg-opacity-10 border-start border-danger border-3' : '' }}">
                        <span class="badge mt-1 {{ $worker->level === 2 && !$worker->status->isTerminal() ? 'bg-danger' : 'bg-light text-muted border' }}"
                              style="min-width:2rem;">N2</span>
                        <div>
                            @if($worker->worker_type === 'direct')
                            <div class="fw-semibold small">CNGP</div>
                            <div class="text-muted small">2 semaines max (14j)</div>
                            @else
                            <div class="fw-semibold small">Spécialiste MGP UGP → RH</div>
                            <div class="text-muted small">7 jours calendaires</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Récapitulatif SLA ──────────────────────────────────────────── --}}
            @if($slaRatio !== null)
            <div class="card mb-4">
                <div class="card-header py-3">
                    <span class="fw-semibold">
                        <i data-feather="shield" style="width:16px;height:16px;" class="me-2"></i>
                        Récapitulatif SLA
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-2 text-center mb-3">
                        <div class="col-6">
                            <div class="small text-muted">Échéance</div>
                            <div class="fw-semibold">{{ $worker->sla_deadline->format('d/m/Y') }}</div>
                        </div>
                        <div class="col-6">
                            <div class="small text-muted">Consommé</div>
                            <div class="fw-bold {{ $slaRatio >= 1 ? 'text-danger' : ($slaRatio >= 0.75 ? 'text-warning' : 'text-success') }}">
                                {{ round($slaRatio * 100) }}%
                            </div>
                        </div>
                    </div>
                    @if($worker->isSlaBreached())
                        <div class="alert alert-danger py-2 mb-0 text-center">
                            <i data-feather="alert-triangle" style="width:13px;height:13px;" class="me-1"></i>
                            SLA dépassé - action requise
                        </div>
                    @elseif($slaDaysLeft !== null)
                        <div class="alert {{ $slaRatio >= 0.75 ? 'alert-warning' : 'alert-success' }} py-2 mb-0 text-center">
                            <i data-feather="clock" style="width:13px;height:13px;" class="me-1"></i>
                            <strong>{{ $slaDaysLeft }}</strong> jour{{ $slaDaysLeft > 1 ? 's' : '' }} restant{{ $slaDaysLeft > 1 ? 's' : '' }}
                        </div>
                    @endif
                </div>
            </div>
            @endif

        </div>
    </div>

</x-layout.app>
