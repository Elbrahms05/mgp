<x-layout.app :title="$report->code . ' - VBG PACIPA'">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.index') }}">VBG</a></li>
        <li class="breadcrumb-item active">{{ $report->code }}</li>
    </x-slot>

    {{-- Bandeau accès restreint --}}
    <div style="background:#b71c1c;color:white;border-radius:8px;padding:10px 20px;margin-bottom:12px;font-size:0.85rem;margin-top:24px;margin-bottom:16px;">
        <i data-feather="lock" style="width:14px;height:14px;"></i>
        <strong>DONNÉES CONFIDENTIELLES</strong> - Accès enregistré dans le journal d'audit.
        Toute divulgation non autorisée est une violation du protocole VBG/PACIPA.
    </div>

    {{-- Header --}}
    <div class="card" style="border-radius:8px;margin-bottom:0;background:#DC3545;color:white;">
        <div class="card-body" style="padding:20px 24px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="font-size:0.8rem;opacity:0.8;">Signalement VBG/EAS/HS</div>
                    <h5 style="margin:4px 0;">{{ $report->code }}</h5>
                    <div style="opacity:0.85;font-size:0.9rem;">
                        {{ $report->channel?->label() ?? '-' }}
                        &bull;
                        Reçu le {{ $report->created_at->format('d/m/Y à H:i') }}
                    </div>
                </div>
                <div style="text-align:right;">
                    <div style="margin-bottom:8px;">
                        @if($report->ugp_notified_at)
                        <span style="background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:12px;font-size:0.8rem;">
                            <i data-feather="bell" style="width:12px;height:12px;"></i> UGP notifiée {{ $report->ugp_notified_at->format('d/m H:i') }}
                        </span>
                        @else
                        <span style="background:rgba(255,0,0,0.3);padding:2px 10px;border-radius:12px;font-size:0.8rem;">
                            <i data-feather="alert-triangle" style="width:12px;height:12px;"></i> UGP non notifiée - URGENT
                        </span>
                        @endif
                    </div>
                    <div class="d-flex gap-2 justify-content-end flex-wrap">
                        @can('vbg.receive')
                        @if(! $report->referrals->count())
                        <a href="{{ route('vbg.referral', $report) }}"
                           class="btn btn-sm" style="background:white;color:#DC3545;">
                            <i data-feather="activity" style="width:14px;height:14px;"></i> Référencer
                        </a>
                        @endif
                        @endcan
                        @can('vbg.manage_enquiry')
                        @if(! $report->enquiry)
                        <a href="{{ route('vbg.enquiry', $report) }}"
                           class="btn btn-sm" style="background:white;color:#DC3545;">
                            <i data-feather="search" style="width:14px;height:14px;"></i> Commission d'enquête
                        </a>
                        @endif
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row g-3 mt-2">

        {{-- Colonne principale --}}
        <div class="col-12 col-md-8">

            {{-- Données Partie 1 --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 text-muted">
                        <i data-feather="user" style="width:16px;height:16px;"></i> Partie 1 - Identification
                    </h5>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:40%;">Canal</td>
                                <td>{{ $report->channel?->label() ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Survivant(e)</td>
                                <td>
                                    @php
                                        $agLabels = ['girl_lt18' => 'Fille (< 18 ans)', 'woman_gte18' => 'Femme (≥ 18 ans)', 'boy_lt18' => 'Garçon (< 18 ans)', 'man_gte18' => 'Homme (≥ 18 ans)'];
                                    @endphp
                                    {{ $agLabels[$report->survivor_age_group] ?? '-' }},
                                    {{ $report->survivor_gender === 'F' ? 'Féminin' : 'Masculin' }}
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Téléphone</td>
                                <td>
                                    <x-vbg.encrypted-field
                                        :value="$decryptedPhone"
                                        :canDecrypt="$canDecrypt"
                                        label="Téléphone"
                                    />
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Anonyme</td>
                                <td>{{ $report->is_anonymous ? 'Oui' : 'Non' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Consentement éclairé</td>
                                <td>
                                    @if($report->consent_given)
                                    <span class="text-success">
                                        <i data-feather="check-circle" style="width:14px;height:14px;"></i>
                                        Oui - {{ $report->consent_given_at?->format('d/m/Y H:i') }}
                                    </span>
                                    @else
                                    <span class="text-warning">
                                        <i data-feather="alert-circle" style="width:14px;height:14px;"></i> Non confirmé
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Site PACIPA concerné</td>
                                <td>
                                    @if($report->site)
                                    <a href="{{ route('sites.show', $report->site) }}" style="color:#E05206;">
                                        {{ $report->site->name }}
                                    </a>
                                    @else
                                    <em class="text-muted">Non lié à un site PACIPA</em>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Reçu par</td>
                                <td>{{ $report->receivedBy?->name ?? '-' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Données Partie 2 (si consentement) --}}
            @if($report->consent_given)
            <div class="card" style="border-radius:8px;border-left:4px solid #1b5e20;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 text-muted">
                        <i data-feather="file-text" style="width:16px;height:16px;"></i> Partie 2 - Détails incident
                        <span style="font-size:0.8rem;color:#9e9e9e;">(consentement confirmé)</span>
                    </h5>
                    <table class="table table-striped">
                        <tbody>
                            <tr>
                                <td class="text-muted" style="width:40%;">Date incident</td>
                                <td>{{ $report->incident_date?->format('d/m/Y') ?? 'Non précisée' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Zone incident</td>
                                <td>{{ $report->incident_zone ?? 'Non précisée' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Lien auteur / projet</td>
                                <td>{{ $report->author_link?->label() ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Classification GBVIMS</td>
                                <td>
                                    @if($report->classification)
                                    <strong class="text-danger">{{ $report->classification->label() }}</strong>
                                    @else
                                    <em class="text-muted">Non classifiée</em>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="card" style="border-radius:8px;background:#f5f5f5;">
                <div class="card-body text-center text-muted" style="padding:32px;">
                    <i data-feather="lock" style="width:32px;height:32px;"></i>
                    <p>Partie 2 non disponible - Consentement non confirmé</p>
                </div>
            </div>
            @endif

        </div>

        {{-- Colonne droite : actions et suivi --}}
        <div class="col-12 col-md-4">

            {{-- Référencements --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 text-muted" style="font-size:1rem;">
                        <i data-feather="activity" style="width:16px;height:16px;"></i>
                        Référencements ({{ $report->referrals->count() }})
                    </h5>
                    @if($report->referrals->isEmpty())
                    <p class="text-muted" style="font-size:0.85rem;">
                        Aucun référencement. Délai : {{ config('vbg.referral_hours', 72) }}h.
                    </p>
                    @else
                    @foreach($report->referrals as $ref)
                    <div style="border-bottom:1px solid #e0e0e0;padding:8px 0;font-size:0.85rem;">
                        <strong>{{ $ref->service_type?->label() ?? '-' }}</strong><br>
                        <span class="text-muted">{{ $ref->provider_name }}</span><br>
                        Délai : {{ $ref->deadline_at?->format('d/m/Y H:i') ?? '-' }}
                        @if($ref->confirmed_at)
                        <span class="text-success"> - Confirmé</span>
                        @endif
                    </div>
                    @endforeach
                    @endif
                    @can('vbg.receive')
                    <a href="{{ route('vbg.referral', $report) }}"
                       class="btn btn-danger btn-sm w-100 mt-3">
                        <i data-feather="plus" style="width:14px;height:14px;"></i> Nouveau référencement
                    </a>
                    @endcan
                </div>
            </div>

            {{-- Commission d'enquête --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 text-muted" style="font-size:1rem;">
                        <i data-feather="search" style="width:16px;height:16px;"></i> Commission d'enquête
                    </h5>
                    @if(! $report->enquiry)
                    <p class="text-muted" style="font-size:0.85rem;">
                        Aucune commission ouverte. Délai max : 30 jours.
                    </p>
                    @can('vbg.manage_enquiry')
                    <a href="{{ route('vbg.enquiry', $report) }}"
                       class="btn btn-secondary btn-sm w-100">
                        <i data-feather="search" style="width:14px;height:14px;"></i> Ouvrir une commission
                    </a>
                    @endcan
                    @else
                    <table class="table table-striped" style="font-size:0.85rem;">
                        <tbody>
                            <tr><td class="text-muted">Ouverte le</td><td>{{ $report->enquiry->opened_at?->format('d/m/Y') }}</td></tr>
                            <tr><td class="text-muted">Délai</td><td>{{ $report->enquiry->deadline_at?->format('d/m/Y') }}</td></tr>
                            <tr>
                                <td class="text-muted">Lien projet confirmé</td>
                                <td>
                                    @if($report->enquiry->project_link_confirmed)
                                    <span class="text-danger"><i data-feather="alert-triangle" style="width:14px;height:14px;"></i> Oui</span>
                                    @else
                                    <span class="text-muted">Non encore</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    @endif
                </div>
            </div>

            {{-- Notifications Banque mondiale --}}
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3 text-muted" style="font-size:1rem;">
                        <i data-feather="bell" style="width:16px;height:16px;"></i> Notifications
                    </h5>
                    <table class="table table-striped" style="font-size:0.85rem;">
                        <tbody>
                            <tr>
                                <td class="text-muted">UGP</td>
                                <td>
                                    @if($report->ugp_notified_at)
                                    <span class="text-success">
                                        ✓ {{ $report->ugp_notified_at->format('d/m H:i') }}
                                    </span>
                                    @else
                                    <span class="text-danger fw-bold">⚠ En attente</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted">Banque mondiale</td>
                                <td>
                                    @if($report->worldbank_notified_at)
                                    <span class="text-success">
                                        ✓ {{ $report->worldbank_notified_at->format('d/m H:i') }}
                                    </span>
                                    @else
                                    <span class="text-warning">En attente</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

</x-layout.app>
