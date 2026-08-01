<x-layout.app title="Nouveau comité"
description="Créer un comité"
icon="user-plus">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Comités</a></li>
        <li class="breadcrumb-item active">Nouveau comité</li>
    </x-slot>

    <div x-data="committeeForm()" x-init="init()" class="margin-row">
        <div class="row g-3">
            <div class="col-12 col-lg-12 offset-lg-12">

                
                <form method="POST" action="{{ route('committees.store') }}">
                    @csrf

                    {{-- Bloc 1 : Identification --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3 text-muted">
                                <i data-feather="info" style="width:16px;height:16px;"></i> Identification du comité
                            </h5>
                            <div class="row g-3">
                                <div class="col-12 col-md-8">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom du comité *</label>
                                        <input id="name" name="name" type="text" maxlength="200"
                                               value="{{ old('name') }}"
                                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="mb-3">
                                        <label for="level" class="form-label">Niveau *</label>
                                        <select name="level" id="level" x-model="level" @change="onLevelChange()" required
                                                class="form-select {{ $errors->has('level') ? 'is-invalid' : '' }}">
                                            <option value="" disabled selected>-- Niveau --</option>
                                            @foreach($levels as $l)
                                            <option value="{{ $l->value }}" {{ old('level') === $l->value ? 'selected' : '' }}>
                                                {{ $l->value }} - {{ ['cbgp'=>'Base','ccgp'=>'Communal','cngp'=>'National'][$l->value] ?? '' }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="installation_date" class="form-label">Date d'installation *</label>
                                        <input id="installation_date" name="installation_date" type="date"
                                               value="{{ old('installation_date') }}"
                                               class="form-control {{ $errors->has('installation_date') ? 'is-invalid' : '' }}" required>
                                        @error('installation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-center" style="padding-top:8px;">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_active" name="is_active" value="1"
                                               class="form-check-input"
                                               {{ old('is_active', '1') ? 'checked' : '' }}>
                                        <label for="is_active" class="form-check-label">Comité actif</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="3"
                                          maxlength="2000">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bloc 2 : Localisation (masqué pour CNGP) --}}
                    <div class="card" style="border-radius:8px;" x-show="level !== 'cngp'">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3 text-muted">
                                <i data-feather="map-pin" style="width:16px;height:16px;"></i>
                                Localisation <span x-text="level === 'cbgp' ? '(Localité)' : '(Commune)'"></span>
                            </h5>
                            <x-form.cascade-select
                                :regions="$regions"
                                :selected="[]"
                                :showSite="false"
                                :required="false"
                            />
                        </div>
                    </div>

                    {{-- Bloc 3 : Membres --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                                <h5 class="fw-semibold mb-0 text-muted">
                                    <i data-feather="users" style="width:16px;height:16px;"></i> Membres du comité
                                </h5>
                                <button type="button" @click="addMember()"
                                        class="btn btn-primary btn-sm">
                                    <i data-feather="user-plus" style="width:14px;height:14px;"></i> Ajouter un membre
                                </button>
                            </div>

                            {{-- Alerte points focaux VBG --}}
                            <div x-show="!hasVbgFocalPairM || !hasVbgFocalPairF"
                                 class="alert alert-warning" style="border-radius:6px;">
                                <i data-feather="alert-triangle" style="width:16px;height:16px;" class="me-1"></i>
                                Le comité doit avoir au moins <strong>1 point focal VBG homme</strong> et
                                <strong>1 point focal VBG femme</strong> (exigence NES-2 Banque mondiale).
                            </div>

                            @error('members')
                            <div class="alert alert-danger" style="border-radius:6px;">
                                {{ $message }}
                            </div>
                            @enderror

                            <div x-show="members.length === 0" class="text-muted text-center" style="padding:24px;">
                                <i data-feather="user" style="width:32px;height:32px;"></i>
                                <p>Aucun membre ajouté. Utilisez le bouton ci-dessus.</p>
                            </div>

                            <template x-for="(member, idx) in members" :key="idx">
                                <div class="card mb-2" style="border-radius:6px;background:#f8f9fa;">
                                    <div class="card-body" style="padding:12px 16px;">
                                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                                            <strong x-text="'Membre ' + (idx+1)"></strong>
                                            <button type="button" @click="removeMember(idx)"
                                                    class="btn btn-link btn-sm text-danger p-0">
                                                <i data-feather="trash-2" style="width:16px;height:16px;"></i>
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-12 col-md-5">
                                                <input :name="'members[' + idx + '][full_name]'"
                                                       :id="'member_name_' + idx"
                                                       type="text" maxlength="200"
                                                       x-model="member.full_name" required
                                                       placeholder="Nom complet *"
                                                       class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <input :name="'members[' + idx + '][role]'"
                                                       :id="'member_role_' + idx"
                                                       type="text" maxlength="100"
                                                       x-model="member.role" required
                                                       placeholder="Rôle *"
                                                       class="form-control form-control-sm">
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <select :name="'members[' + idx + '][gender]'"
                                                        :id="'member_gender_' + idx"
                                                        x-model="member.gender"
                                                        @change="recomputeVbgFocal()"
                                                        required
                                                        class="form-select form-select-sm">
                                                    <option value="">Genre *</option>
                                                    <option value="M">Homme</option>
                                                    <option value="F">Femme</option>
                                                </select>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <input :name="'members[' + idx + '][phone]'"
                                                       :id="'member_phone_' + idx"
                                                       type="tel" maxlength="20"
                                                       x-model="member.phone"
                                                       placeholder="Téléphone"
                                                       class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-md-5">
                                                <input :name="'members[' + idx + '][election_mode]'"
                                                       :id="'member_mode_' + idx"
                                                       type="text" maxlength="100"
                                                       x-model="member.election_mode" required
                                                       placeholder="Mode d'élection/désignation *"
                                                       class="form-control form-control-sm">
                                            </div>
                                            <div class="col-12 col-md-3 d-flex align-items-center">
                                                <div class="form-check">
                                                    <input type="checkbox"
                                                           :name="'members[' + idx + '][is_vbg_focal_point]'"
                                                           value="1"
                                                           x-model="member.is_vbg_focal_point"
                                                           @change="recomputeVbgFocal()"
                                                           class="form-check-input">
                                                    <label class="form-check-label" style="color:#6f42c1;font-weight:500;">
                                                        Point focal VBG
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('committees.index') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success">
                            <i data-feather="save" style="width:16px;height:16px;"></i> Créer le comité
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function committeeForm() {
        return {
            level: '{{ old('level', '') }}',
            members: [],
            hasVbgFocalPairM: true,
            hasVbgFocalPairF: true,

            init() {
                this.$watch('level', () => {});
            },

            onLevelChange() {},

            addMember() {
                this.members.push({
                    full_name: '', role: '', gender: '', phone: '', election_mode: '', is_vbg_focal_point: false
                });
            },

            removeMember(idx) {
                this.members.splice(idx, 1);
                this.recomputeVbgFocal();
            },

            recomputeVbgFocal() {
                const focal = this.members.filter(m => m.is_vbg_focal_point);
                this.hasVbgFocalPairM = focal.some(m => m.gender === 'M');
                this.hasVbgFocalPairF = focal.some(m => m.gender === 'F');
            },
        };
    }
    </script>
    @endpush

</x-layout.app>
