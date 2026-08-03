{{-- Enregistrement du composant Alpine AVANT Alpine.start() (app.js est type=module donc différé) --}}
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('formPicker', (odkForms, registeredIds, oldFormId, oldName) => ({
        odkForms,
        registeredIds,
        selected: oldFormId,
        name: oldName,
        selectedVersion: '',
        _prevOdkName: '',

        init() {
            // Restaurer état si old() présent (retour après erreur de validation)
            if (this.selected) {
                const f = this.odkForms.find(f => f.xmlFormId === this.selected);
                if (f) {
                    this._prevOdkName    = f.name || '';
                    this.selectedVersion = f.version || '';
                    if (!this.name) this.name = f.name || '';
                }
            }

            // Réagir au changement de sélection
            this.$watch('selected', (value) => {
                const f = this.odkForms.find(f => f.xmlFormId === value);
                if (f) {
                    if (!this.name || this.name === this._prevOdkName) {
                        this.name = f.name || '';
                    }
                    this._prevOdkName    = f.name || '';
                    this.selectedVersion = f.version || '';
                } else {
                    this.selectedVersion = '';
                }
            });
        },
    }));
});
</script>

<x-layout.app title="Nouveau formulaire ODK">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('forms.index') }}">Formulaires ODK</a></li>
        <li class="breadcrumb-item active">Nouveau</li>
    </x-slot>

    <div class="row justify-content-center margin-row"
         x-data="formPicker(
             {{ Js::from($odkForms) }},
             {{ Js::from($registeredIds) }},
             {{ Js::from(old('form_id', '')) }},
             {{ Js::from(old('name', '')) }}
         )">

        <div class="col-12 col-lg-12 offset-lg-12">

            {{-- Alerte si ODK Central inaccessible --}}
            @if($odkError)
            <div class="alert alert-warning py-2 mb-3">
                <i data-feather="wifi-off" style="width:14px;height:14px;" class="me-1"></i>
                ODK Central inaccessible - impossible de charger la liste des formulaires.
                <span class="text-muted small ms-2">{{ Str::limit($odkError, 100) }}</span>
            </div>
            @endif

            <div class="card" style="border-radius:8px;">
                <div class="card-header py-3">
                    <span class="fw-semibold" style="color:white;">
                        <i data-feather="file-plus" style="width:16px;height:16px;" class="me-2"></i>
                        Associer un formulaire ODK Central
                    </span>
                </div>
                <div class="card-body">

                    <form method="POST" action="{{ route('forms.store') }}">
                        @csrf

                        {{-- Sélection du formulaire ODK --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">
                                Formulaire ODK Central <span class="text-danger">*</span>
                            </label>

                            @if(empty($odkForms))
                            <div class="alert alert-secondary py-2">
                                <i data-feather="alert-circle" style="width:14px;height:14px;" class="me-1"></i>
                                Aucun formulaire disponible sur ODK Central.
                            </div>
                            @else
                            <select class="form-select @error('form_id') is-invalid @enderror"
                                    x-model="selected"
                                    required>
                                <option value="">- Sélectionner un formulaire -</option>
                                @foreach($odkForms as $f)
                                @php $already = in_array($f['xmlFormId'], $registeredIds); @endphp
                                <option value="{{ $f['xmlFormId'] }}"
                                        {{ $already ? 'disabled' : '' }}
                                        {{ old('form_id') === $f['xmlFormId'] ? 'selected' : '' }}>
                                    {{ $f['xmlFormId'] }}
                                    @if(!empty($f['name'])) - {{ $f['name'] }} @endif
                                    @if(!empty($f['version'])) (v{{ $f['version'] }}) @endif
                                    @if($already) · déjà enregistré @endif
                                </option>
                                @endforeach
                            </select>
                            @error('form_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror

                            <div x-show="selected" class="mt-2 small text-muted" x-cloak>
                                <i data-feather="info" style="width:12px;height:12px;" class="me-1"></i>
                                ID ODK : <code x-text="selected"></code>
                                <span x-show="selectedVersion">
                                    - version <strong x-text="selectedVersion"></strong>
                                </span>
                            </div>
                            @endif

                            <input type="hidden" name="form_id" x-model="selected">
                        </div>

                        {{-- Type MGP (F01–F07) - détermine la logique de traitement à la sync --}}
                        <div class="mb-3">
                            <label for="form_type" class="form-label fw-semibold">
                                Type MGP <span class="text-danger">*</span>
                            </label>
                            <select id="form_type" name="form_type"
                                    class="form-select @error('form_type') is-invalid @enderror"
                                    required>
                                <option value="">- Sélectionner le type de formulaire -</option>
                                <option value="F01" {{ old('form_type') === 'F01' ? 'selected' : '' }}>F01 - Dépôt de plainte (Annexe 4)</option>
                                <option value="F02" {{ old('form_type') === 'F02' ? 'selected' : '' }}>F02 - Plainte non sensible (Annexe 8)</option>
                                <option value="F03" {{ old('form_type') === 'F03' ? 'selected' : '' }}>F03 - Rapport VBG/EAS/HS (Annexe 9)</option>
                                <option value="F04" {{ old('form_type') === 'F04' ? 'selected' : '' }}>F04 - PV de conciliation (Annexe 5)</option>
                                <option value="F05" {{ old('form_type') === 'F05' ? 'selected' : '' }}>F05 - Suivi périodique (Annexe 6)</option>
                                <option value="F06" {{ old('form_type') === 'F06' ? 'selected' : '' }}>F06 - PV installation comité (Annexe 3)</option>
                                <option value="F07" {{ old('form_type') === 'F07' ? 'selected' : '' }}>F07 - Évaluation trimestrielle (Annexe 10)</option>
                            </select>
                            @error('form_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Détermine comment les soumissions ODK sont traitées lors de la synchronisation.</div>
                        </div>

                        {{-- Nom (auto-rempli depuis ODK, éditable) --}}
                        <div class="mb-3">
                            <label for="name" class="form-label fw-semibold">
                                Nom affiché <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   x-model="name"
                                   placeholder="Nom du formulaire"
                                   required>
                            @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Pré-rempli depuis ODK Central, modifiable si besoin.</div>
                        </div>

                        {{-- Description --}}
                        <div class="mb-3">
                            <label for="description" class="form-label fw-semibold">Description / Référence MGP</label>
                            <textarea id="description"
                                      name="description"
                                      class="form-control"
                                      rows="3"
                                      placeholder="ex: F01 - Dépôt de plainte (Annexe 4)">{{ old('description') }}</textarea>
                        </div>

                        {{-- Statut --}}
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox"
                                       id="is_active"
                                       name="is_active"
                                       class="form-check-input"
                                       value="1"
                                       {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">Formulaire actif</label>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('forms.index') }}" class="btn btn-secondary btn-sm">
                                <i data-feather="x" class="me-1" style="width:14px;height:14px;"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-sm" :disabled="!selected">
                                <i data-feather="save" class="me-1" style="width:14px;height:14px;"></i> Enregistrer
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</x-layout.app>
