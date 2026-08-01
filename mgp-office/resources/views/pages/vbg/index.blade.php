<x-layout.app 
    title="Signalements VBG / EAS / HS" 
    description="Données confidentielles - Accès restreint et journalisé"
    icon="shield-alert">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">VBG / EAS / HS</li>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('vbg.create') }}" class="btn btn-warning btn-sm">
            <i data-feather="plus" style="width:16px;height:16px;"></i> Nouveau signalement
        </a>
    </x-slot>

    {{-- Bandeau accès restreint --}}
    <div style="background:#b71c1c;color:white;border-radius:8px;padding:12px 20px;margin-top:24px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <i data-feather="shield" style="width:20px;height:20px;"></i>
        <strong>ACCÈS RESTREINT - Données confidentielles VBG/EAS/HS</strong>
        <span style="font-size:0.85rem;opacity:0.9;">
            Toute consultation est enregistrée dans le journal d'audit.
        </span>
    </div>

    <div x-data="vbgIndex()" x-init="init()">

        {{-- Filtres --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('vbg.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="filter_channel" class="form-label">Canal</label>
                                <select name="channel" id="filter_channel" class="form-select">
                                    <option value="">Tous les canaux</option>
                                    @foreach($channels as $ch)
                                    <option value="{{ $ch->value }}" {{ request('channel') === $ch->value ? 'selected' : '' }}>
                                        {{ $ch->label() }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="date_from" class="form-label">Du</label>
                                <input type="date" id="date_from" name="date_from"
                                       value="{{ request('date_from') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="date_to" class="form-label">Au</label>
                                <input type="date" id="date_to" name="date_to"
                                       value="{{ request('date_to') }}" class="form-control">
                            </div>
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end pb-3">
                            <button type="submit" class="btn btn-danger">
                                <i data-feather="search" style="width:16px;height:16px;"></i>
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

        {{-- Tableau - données minimales (pas d'identifiants) --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($reports->isEmpty())
                <div style="text-align:center;padding:48px;color:#9e9e9e;">
                    <i data-feather="shield" style="width:48px;height:48px;"></i>
                    <p>Aucun signalement.</p>
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Code signalement</th>
                            <th>Canal</th>
                            <th class="text-center">Âge</th>
                            <th class="text-center">Genre</th>
                            <th class="text-center">Consentement</th>
                            <th class="text-center">Référencé</th>
                            <th class="text-center">Enquête</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($reports as $report)
                        <tr>
                            <td>
                                <a href="{{ route('vbg.show', $report) }}" class="text-danger">
                                    <code style="font-size:0.8rem;">{{ $report->code }}</code>
                                </a>
                            </td>
                            <td style="font-size:0.85rem;">{{ $report->channel?->label() ?? '-' }}</td>
                            <td class="text-center" style="font-size:0.85rem;">
                                @php
                                    $agLabels = ['girl_lt18' => 'Fille <18', 'woman_gte18' => 'Femme ≥18', 'boy_lt18' => 'Garçon <18', 'man_gte18' => 'Homme ≥18'];
                                @endphp
                                {{ $agLabels[$report->survivor_age_group] ?? '-' }}
                            </td>
                            <td class="text-center">
                                {{ $report->survivor_gender === 'M' ? 'H' : ($report->survivor_gender === 'F' ? 'F' : '-') }}
                            </td>
                            <td class="text-center">
                                @if($report->consent_given)
                                <i data-feather="check-circle" style="width:16px;height:16px;color:#009A44;"></i>
                                @else
                                <i data-feather="minus-circle" style="width:16px;height:16px;color:#6C757D;"></i>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($report->referrals_count ?? $report->relationLoaded('referrals') && $report->referrals->isNotEmpty())
                                <i data-feather="activity" style="width:16px;height:16px;color:#17A2B8;"></i>
                                @else
                                <span class="text-muted" style="font-size:0.8rem;">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($report->enquiry)
                                <i data-feather="search" style="width:16px;height:16px;color:#6f42c1;"></i>
                                @else
                                <span class="text-muted" style="font-size:0.8rem;">-</span>
                                @endif
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $report->created_at->format('d/m/Y') }}
                            </td>
                            <td class="text-end">
                                <a href="{{ route('vbg.show', $report) }}" class="btn btn-link btn-sm text-danger">
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
            @if($reports->hasPages())
            <div class="card-footer text-center">
                {{ $reports->links() }}
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
    function vbgIndex() {
        return {
            init() {}
        };
    }
    </script>
    @endpush

</x-layout.app>
