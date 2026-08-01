<x-layout.app :title="($complaint->registration_number ?? '#'.$complaint->id) . ' - Plaintes PACIPA'">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item active">{{ $complaint->registration_number ?? '#'.$complaint->id }}</li>
    </x-slot>
    

    @php
        $statusColor = $complaint->status->colorClass();
    @endphp

    {{-- Header --}}
    <div class="card text-white {{ $isBreached ? '' : '' }}" style="border-radius:8px;margin-bottom:0;background-color:#0DB02B;margin-top:24px;">
        <div class="card-body" style="padding:20px 24px;">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div style="margin-top:24px;margin-bottom:16px;">
                    <div style="font-size:0.8rem;opacity:0.8;margin-bottom:4px;">
                        <code style="background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:4px;color:white;font-weight:600;">
                            {{ $complaint->registration_number ?? 'Non assigné' }}
                        </code>
                        @if($isBreached)
                        <span style="background:#c62828;padding:2px 8px;border-radius:4px;margin-left:6px;font-weight:700;">
                            ⚠ SLA DÉPASSÉ
                        </span>
                        @endif
                    </div>
                    <h5 style="margin:0 0 4px;color:white;font-weight:600;">{{ Str::limit($complaint->subject, 80) }}</h5>
                    <div style="opacity:0.85;font-size:0.9rem;">
                        <x-complaint.type-icon :type="$complaint->complaintType" />
                        {{ $complaint->complaintType?->label ?? '-' }}
                        @if($complaint->site)
                        &bull; <i data-feather="map-pin" style="width:12px;height:12px;"></i> {{ $complaint->site->name }}
                        @endif
                    </div>
                </div>
                <div class="text-end">
                    <div style="font-size:1rem;font-weight:600;margin-bottom:4px;">
                        <x-complaint.status-badge :status="$complaint->status" />
                    </div>
                    <div style="margin-top:6px;font-size:0.85rem;opacity:0.8;">
                        Niveau : {{ $complaint->current_level?->value ?? '-' }}
                    </div>
                    <div style="margin-top:8px;">
                        <x-complaint.sla-countdown
                            :complaint="$complaint"
                            :hoursRemaining="$hoursRemaining"
                            :isBreached="$isBreached"
                        />
                    </div>
                    @can('complaints.edit')
                    @if(! $complaint->status->isTerminal())
                    <div style="margin-top:8px;">
                        <a href="{{ route('complaints.edit', $complaint) }}"
                           class="btn btn-light btn-sm">
                            <i data-feather="edit-2" class="me-1" style="width:13px;height:13px;"></i>Modifier
                        </a>
                    </div>
                    @endif
                    @endcan
                </div>
            </div>
        </div>
    </div>

 

    <div class="row mt-4">

        {{-- Colonne principale --}}
        <div class="col-12 col-md-8">

            {{-- Détails --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">
                        <i data-feather="info" class="me-1" style="width:16px;height:16px;"></i>Détails de la plainte
                    </h5>
                    <table class="table table-striped table-sm">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:40%;">Plaignant</td>
                                <td>
                                    @if($complaint->is_anonymous)
                                    <em class="text-muted">Anonyme</em>
                                    @else
                                    {{ $complaint->complainant_name ?? '-' }}
                                    @if($complaint->complainant_age)
                                    ({{ $complaint->complainant_age }} ans)
                                    @endif
                                    @if($complaint->complainant_gender)
                                    - {{ $complaint->complainant_gender === 'M' ? 'Homme' : 'Femme' }}
                                    @endif
                                    @endif
                                </td>
                            </tr>
                            @if(! $complaint->is_anonymous && $complaint->complainant_phone)
                            <tr>
                                <td class="text-muted">Téléphone</td>
                                <td>{{ $complaint->complainant_phone }}</td>
                            </tr>
                            @endif
                            @if(! $complaint->is_anonymous && $complaint->complainant_locality)
                            <tr>
                                <td class="text-muted">Localité plaignant</td>
                                <td>{{ $complaint->complainant_locality }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Date des faits</td>
                                <td>{{ $complaint->incident_date?->format('d/m/Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Canal de réception</td>
                                <td>{{ $complaint->channel?->label() ?? '-' }}</td>
                            </tr>
                            @if($complaint->accused_party)
                            <tr>
                                <td class="text-muted">Partie mise en cause</td>
                                <td>{{ $complaint->accused_party }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td class="text-muted">Enregistré le</td>
                                <td>{{ $complaint->created_at->format('d/m/Y H:i') }} par {{ $complaint->registeredBy?->name ?? '-' }}</td>
                            </tr>
                            @if($complaint->sla_deadline)
                            <tr>
                                <td class="text-muted">Échéance SLA</td>
                                <td class="{{ $isBreached ? 'text-danger fw-bold' : '' }}">
                                    {{ $complaint->sla_deadline->format('d/m/Y H:i') }}
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                    <hr>
                    <strong class="text-muted">Description :</strong>
                    <p style="white-space:pre-wrap;margin-top:8px;">{{ $complaint->description }}</p>
                </div>
            </div>

            {{-- Timeline historique --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">
                        <i data-feather="clock" class="me-1" style="width:16px;height:16px;"></i>Historique des actions
                    </h5>
                    @if($complaint->histories->isEmpty())
                    <p class="text-muted">Aucune action enregistrée.</p>
                    @else
                    <div style="position:relative;padding-left:32px;">
                        <div style="position:absolute;left:12px;top:0;bottom:0;width:2px;background:#e0e0e0;"></div>
                        @foreach($complaint->histories->sortByDesc('created_at') as $history)
                        <div style="position:relative;margin-bottom:20px;">
                            <div style="position:absolute;left:-28px;top:4px;width:12px;height:12px;border-radius:50%;background:#0DB02B;border:2px solid white;"></div>
                            <div style="font-size:0.8rem;color:#9e9e9e;">
                                {{ $history->created_at->format('d/m/Y H:i') }}
                                - {{ $history->performedBy?->name ?? 'Système' }}
                            </div>
                            <div style="margin-top:4px;">
                                @if($history->from_status)
                                <span class="text-muted" style="font-size:0.85rem;">
                                    {{ $history->from_status->label() }} →
                                </span>
                                @endif
                                <strong style="font-size:0.9rem;">{{ $history->to_status?->label() ?? $history->action }}</strong>
                            </div>
                            @if($history->comment)
                            <div style="font-size:0.85rem;color:#555;margin-top:4px;font-style:italic;">
                                "{{ $history->comment }}"
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Colonne actions --}}
        <div class="col-12 col-md-4">

            {{-- Actions workflow --}}
            @if(! $complaint->status->isTerminal())
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">
                        <i data-feather="play" class="me-1" style="width:14px;height:14px;"></i>Actions disponibles
                    </h6>

                    {{-- Mettre en examen --}}
                    @if($complaint->status === \App\Domains\Complaint\Enums\ComplaintStatus::REGISTERED)
                    <form method="POST" action="{{ route('complaints.accept', $complaint) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary w-100 mb-2">
                            <i data-feather="check" class="me-1" style="width:14px;height:14px;"></i>Déclarer recevable
                        </button>
                    </form>
                    @endif

                    {{-- Médiation --}}
                    @if($complaint->status === \App\Domains\Complaint\Enums\ComplaintStatus::ACCEPTED)
                    <form method="POST" action="{{ route('complaints.mediation', $complaint) }}">
                        @csrf
                        <button type="submit" class="btn btn-warning w-100 mb-2">
                            <i data-feather="users" class="me-1" style="width:14px;height:14px;"></i>Engager médiation
                        </button>
                    </form>
                    @endif

                    {{-- Résoudre --}}
                    @if(in_array($complaint->status, [
                        \App\Domains\Complaint\Enums\ComplaintStatus::IN_MEDIATION,
                        \App\Domains\Complaint\Enums\ComplaintStatus::ACCEPTED,
                    ]))
                    <form method="POST" action="{{ route('complaints.resolve', $complaint) }}" x-data="{ comment: '' }">
                        @csrf
                        <div class="mb-2">
                            <textarea name="comment" class="form-control" x-model="comment"
                                      rows="3" placeholder="Commentaire résolution (optionnel)"></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100 mb-2">
                            <i data-feather="check-circle" class="me-1" style="width:14px;height:14px;"></i>Marquer résolue
                        </button>
                    </form>
                    @endif

                    {{-- Clôturer --}}
                    @if($complaint->status === \App\Domains\Complaint\Enums\ComplaintStatus::RESOLVED)
                    <form method="POST" action="{{ route('complaints.close', $complaint) }}">
                        @csrf
                        <button type="submit" class="btn btn-secondary w-100 mb-2">
                            <i data-feather="lock" class="me-1" style="width:14px;height:14px;"></i>Clôturer
                        </button>
                    </form>
                    @endif

                    {{-- Escalader --}}
                    @can('complaints.escalate')
                    @if(! in_array($complaint->current_level?->value, [\App\Domains\Committee\Enums\CommitteeLevel::CNGP->value]))
                    <a href="{{ route('complaints.escalate', $complaint) }}"
                       class="btn btn-danger w-100 mb-2 d-block text-center">
                        <i data-feather="arrow-up" class="me-1" style="width:14px;height:14px;"></i>Escalader
                    </a>
                    @endif
                    @endcan

                    {{-- Rejeter --}}
                    @if(in_array($complaint->status, [
                        \App\Domains\Complaint\Enums\ComplaintStatus::REGISTERED,
                        \App\Domains\Complaint\Enums\ComplaintStatus::UNDER_REVIEW,
                    ]))
                    <form method="POST" action="{{ route('complaints.reject', $complaint) }}" x-data="{ reason: '' }">
                        @csrf
                        <div class="mb-2">
                            <textarea name="reason" class="form-control" x-model="reason"
                                      rows="3" placeholder="Motif du rejet *" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-secondary w-100"
                                onclick="return confirm('Confirmer le rejet de cette plainte ?')">
                            <i data-feather="x" class="me-1" style="width:14px;height:14px;"></i>Rejeter
                        </button>
                    </form>
                    @endif

                </div>
            </div>
            @endif

            {{-- Informations complémentaires --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h6 class="fw-semibold mb-3">
                        <i data-feather="info" class="me-1" style="width:14px;height:14px;"></i>Informations
                    </h6>
                    <table class="table table-striped table-sm">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="font-size:0.85rem;">Site</td>
                                <td style="font-size:0.85rem;">
                                    @if($complaint->site)
                                    <a href="{{ route('sites.show', $complaint->site) }}" class="text-primary">
                                        {{ $complaint->site->name }}
                                    </a>
                                    @else
                                    <em class="text-muted">Hors site</em>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted" style="font-size:0.85rem;">Responsable</td>
                                <td style="font-size:0.85rem;">{{ $complaint->assignedUser?->name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</x-layout.app>
