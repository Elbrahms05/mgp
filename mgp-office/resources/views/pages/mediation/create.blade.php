<x-layout.app
    title="Nouvelle médiation"
    description="Formulaire F04 - PV de conciliation (Annexe 5 MGP)"
    icon="users">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('mediation.index') }}">Médiation</a></li>
        <li class="breadcrumb-item active">Nouvelle session</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-lg-12 offset-lg-12">
            <div class="card mb-4"
                 x-data="mediationForm({{ Js::from($committeeMap) }}, {{ $preselectedComplaint?->id ?? 'null' }})">

                

                <div class="card-body">

                    

                    <form method="POST" action="{{ route('mediation.store') }}">
                        @csrf

                        {{-- Plainte ──────────────────────────────────────── --}}
                        <div class="mb-3">
                            <label for="complaint_id" class="form-label">Plainte concernée <span class="text-danger">*</span></label>
                            <select name="complaint_id" id="complaint_id" required
                                    class="form-select @error('complaint_id') is-invalid @enderror"
                                    x-model="complaintId"
                                    @change="onComplaintChange">
                                <option value="">- Sélectionner la plainte -</option>
                                @foreach($complaints as $complaint)
                                <option value="{{ $complaint->id }}"
                                        {{ old('complaint_id', $preselectedComplaint?->id) == $complaint->id ? 'selected' : '' }}>
                                    {{ $complaint->registration_number }} - {{ Str::limit($complaint->subject, 60) }}
                                    ({{ $complaint->site?->name ?? 'Hors site' }})
                                </option>
                                @endforeach
                            </select>
                            @error('complaint_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Comité (affiché dynamiquement) ─────────────── --}}
                        <div x-show="committee !== null" x-cloak class="alert alert-info py-2 mb-3">
                            <i data-feather="git-branch" style="width:14px;height:14px;" class="me-1"></i>
                            Comité associé au site : <strong x-text="committee?.committee_name ?? '-'"></strong>
                        </div>
                        <div x-show="complaintId && committee === null" x-cloak class="alert alert-warning py-2 mb-3">
                            <i data-feather="alert-triangle" style="width:14px;height:14px;" class="me-1"></i>
                            Aucun comité actif associé à ce site. Saisissez manuellement les informations du président.
                        </div>

                        <div class="row g-3">
                            {{-- Date ─────────────────────────────────────── --}}
                            <div class="col-md-6">
                                <label for="session_date" class="form-label">Date de la session <span class="text-danger">*</span></label>
                                <input type="date" id="session_date" name="session_date"
                                       value="{{ old('session_date') }}" required
                                       class="form-control @error('session_date') is-invalid @enderror">
                                @error('session_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Sélection membre (si comité dispo) ────────── --}}
                            <div class="col-md-6" x-show="committee !== null" x-cloak>
                                <label class="form-label">Membre présidant <span class="text-danger">*</span></label>
                                <select class="form-select" x-model="selectedMemberId" @change="onMemberChange">
                                    <option value="">- Sélectionner un membre -</option>
                                    <template x-for="m in committee?.members ?? []" :key="m.id">
                                        <option :value="m.id" x-text="`${m.name} (${m.role})`"></option>
                                    </template>
                                </select>
                                <div class="form-text text-muted">Membres actifs du comité.</div>
                            </div>
                        </div>

                        {{-- Nom + Rôle ───────────────────────────────────── --}}
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label for="presided_by_name" class="form-label">
                                    Présidé par (Nom) <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="presided_by_name" name="presided_by_name"
                                       required
                                       class="form-control @error('presided_by_name') is-invalid @enderror"
                                       x-model="presidedByName"
                                       :readonly="committee !== null && selectedMemberId !== ''"
                                       :class="{ 'bg-light': committee !== null && selectedMemberId !== '' }">
                                @error('presided_by_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="presided_by_role" class="form-label">
                                    Fonction / Rôle <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="presided_by_role" name="presided_by_role"
                                       required
                                       class="form-control @error('presided_by_role') is-invalid @enderror"
                                       x-model="presidedByRole"
                                       :readonly="committee !== null && selectedMemberId !== ''"
                                       :class="{ 'bg-light': committee !== null && selectedMemberId !== '' }">
                                @error('presided_by_role')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12" x-show="committee !== null && selectedMemberId !== ''" x-cloak>
                                <div class="form-text text-muted">
                                    <i data-feather="lock" style="width:11px;height:11px;" class="me-1"></i>
                                    Champs remplis depuis le comité - modifiez le membre sélectionné pour changer.
                                </div>
                            </div>
                        </div>

                        {{-- Résultat ─────────────────────────────────────── --}}
                        <div class="mb-3 mt-3">
                            <label for="result" class="form-label">Compte-rendu / Résultat de la médiation <span class="text-danger">*</span></label>
                            <textarea id="result" name="result" rows="6" required
                                      class="form-control @error('result') is-invalid @enderror">{{ old('result') }}</textarea>
                            @error('result')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Accord ───────────────────────────────────────── --}}
                        <div class="form-check mb-3">
                            <input type="checkbox" id="is_agreement_reached" name="is_agreement_reached" value="1"
                                   class="form-check-input"
                                   {{ old('is_agreement_reached') ? 'checked' : '' }}>
                            <label for="is_agreement_reached" class="form-check-label text-success fw-semibold">
                                Accord trouvé - la plainte sera automatiquement marquée comme résolue
                            </label>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('mediation.index') }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i data-feather="save" style="width:16px;height:16px;" class="me-1"></i>
                                Enregistrer le PV
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function mediationForm(committeeMap, preselectedId) {
        return {
            committeeMap,
            complaintId:      preselectedId ? String(preselectedId) : '',
            committee:        null,
            selectedMemberId: '',
            presidedByName:   '{{ old('presided_by_name') }}',
            presidedByRole:   '{{ old('presided_by_role') }}',

            init() {
                if (this.complaintId) this.loadCommittee();
            },

            onComplaintChange() {
                this.selectedMemberId = '';
                this.presidedByName   = '';
                this.presidedByRole   = '';
                this.loadCommittee();
            },

            loadCommittee() {
                const key = String(this.complaintId);
                const entry = this.committeeMap[key];
                this.committee = (entry !== null && entry !== undefined) ? entry : null;
            },

            onMemberChange() {
                if (!this.selectedMemberId || !this.committee) return;
                const id = parseInt(this.selectedMemberId);
                const m  = this.committee.members.find(x => x.id === id);
                if (m) {
                    this.presidedByName = m.name;
                    this.presidedByRole = m.role;
                }
            },
        };
    }
    </script>
    @endpush

</x-layout.app>
