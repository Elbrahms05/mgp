<x-layout.app title="Nouvelle plainte"
    description="Enregistrer une plainte - Formulaire F01 (Annexe 4)"
    icon="message-square">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item active">Nouvelle plainte</li>
    </x-slot>
    <div style="background:orange;color:white;border-radius:8px;padding:12px 20px;margin-top:24px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
        <i data-feather="shield" style="width:20px;height:20px;"></i>
        Les champs marqués * sont obligatoires. Pour les cas VBG/EAS/HS, utilisez le
        <a href="{{ route('vbg.create') }}" class="text-danger font-weight-bold"><strong>circuit VBG dédié</strong></a>.
    </div>
    <div x-data="complaintForm()" x-init="init()">
        <div class="row">
            <div class="col-12 col-lg-12 offset-lg-12">

                @if($errors->any())
                <div class="alert alert-danger" style="border-radius:8px;">
                    <ul class="mb-0 ps-3">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(session('info'))
                <div class="alert alert-info" style="border-radius:8px;">
                    <i data-feather="info" class="me-2" style="width:16px;height:16px;"></i>{{ session('info') }}
                </div>
                @endif

                <form method="POST" action="{{ route('complaints.store') }}">
                    @csrf

                    {{-- Alerte type VBG --}}
                    <div x-show="isSensitive" x-transition
                        class="alert alert-danger"
                        style="border-radius:8px;border:2px solid #c62828;">
                        <i data-feather="shield" class="me-2" style="width:16px;height:16px;"></i>
                        <strong>Type VBG/EAS/HS détecté.</strong>
                        Ce type de plainte doit être traité via le circuit dédié.
                        <a href="{{ route('vbg.create') }}" class="btn btn-danger btn-sm ms-2">
                            <i data-feather="arrow-right" class="me-1" style="width:13px;height:13px;"></i>Aller au formulaire VBG
                        </a>
                    </div>

                    {{-- Section 1 : Identification (géo + site) --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">1</span>
                                Localisation et site concerné
                            </h5>
                            <x-form.cascade-select
                                :regions="$regions"
                                :selected="old('selected', [])"
                                :showSite="true"
                                siteNullableLabel="Plainte hors site" />
                        </div>
                    </div>

                    {{-- Section 2 : Identité plaignant --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">2</span>
                                Identité du plaignant
                            </h5>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" value="1"
                                        id="is_anonymous"
                                        x-model="isAnonymous"
                                        {{ old('is_anonymous') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">Plainte anonyme</label>
                                </div>
                            </div>

                            <div x-show="!isAnonymous">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="complainant_name" class="form-label">Nom complet du plaignant *</label>
                                            <input id="complainant_name" name="complainant_name" type="text" maxlength="200"
                                                value="{{ old('complainant_name') }}"
                                                class="form-control {{ $errors->has('complainant_name') ? 'is-invalid' : '' }}"
                                                :required="!isAnonymous">
                                            @error('complainant_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="complainant_age" class="form-label">Âge</label>
                                            <input id="complainant_age" name="complainant_age" type="number" min="0" max="120"
                                                value="{{ old('complainant_age') }}"
                                                class="form-control {{ $errors->has('complainant_age') ? 'is-invalid' : '' }}">
                                            @error('complainant_age')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="complainant_gender" class="form-label">Genre</label>
                                            <select name="complainant_gender" id="complainant_gender" class="form-select">
                                                <option value="">-</option>
                                                <option value="M" {{ old('complainant_gender') === 'M' ? 'selected' : '' }}>Homme</option>
                                                <option value="F" {{ old('complainant_gender') === 'F' ? 'selected' : '' }}>Femme</option>
                                            </select>
                                            @error('complainant_gender')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="complainant_phone" class="form-label">Téléphone</label>
                                            <input id="complainant_phone" name="complainant_phone" type="tel" maxlength="20"
                                                value="{{ old('complainant_phone') }}"
                                                class="form-control {{ $errors->has('complainant_phone') ? 'is-invalid' : '' }}">
                                            @error('complainant_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="complainant_locality" class="form-label">Localité du plaignant</label>
                                            <input id="complainant_locality" name="complainant_locality" type="text" maxlength="200"
                                                value="{{ old('complainant_locality') }}"
                                                class="form-control {{ $errors->has('complainant_locality') ? 'is-invalid' : '' }}">
                                            @error('complainant_locality')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 3 : Objet de la plainte --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">3</span>
                                Objet de la plainte
                            </h5>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="complaint_type_id" class="form-label">Type de plainte *</label>
                                        <select name="complaint_type_id" id="complaint_type_id" class="form-select"
                                            @change="onTypeChange($event.target.value)" required>
                                            <option value="" disabled selected>-- Type de plainte --</option>
                                            @foreach($complaintTypes as $ct)
                                            <option value="{{ $ct->id }}"
                                                data-sensitive="{{ $ct->is_sensitive ? 'true' : 'false' }}"
                                                {{ old('complaint_type_id') == $ct->id ? 'selected' : '' }}>
                                                {{ $ct->sort_order }}. {{ $ct->label }}
                                                @if($ct->is_sensitive) 🔒 @endif
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('complaint_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="incident_date" class="form-label">Date des faits *</label>
                                        <input id="incident_date" name="incident_date" type="date"
                                            value="{{ old('incident_date') }}"
                                            max="{{ date('Y-m-d') }}"
                                            class="form-control {{ $errors->has('incident_date') ? 'is-invalid' : '' }}" required>
                                        @error('incident_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Objet (résumé en une phrase) *</label>
                                        <input id="subject" name="subject" type="text" maxlength="300"
                                            value="{{ old('subject') }}"
                                            class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}" required>
                                        @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description détaillée des faits * (min. 20 caractères)</label>
                                        <textarea id="description" name="description" class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                            minlength="20" rows="5"
                                            required>{{ old('description') }}</textarea>
                                        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="accused_party" class="form-label">Partie mise en cause (optionnel)</label>
                                        <input id="accused_party" name="accused_party" type="text" maxlength="300"
                                            value="{{ old('accused_party') }}"
                                            class="form-control {{ $errors->has('accused_party') ? 'is-invalid' : '' }}">
                                        @error('accused_party')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section 4 : Canal --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <span class="badge bg-primary rounded-circle me-2">4</span>
                                Canal de réception
                            </h5>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="channel" class="form-label">Canal de réception *</label>
                                    <select name="channel" id="channel" class="form-select" required>
                                        <option value="" disabled selected>-- Canal --</option>
                                        @foreach($channels as $ch)
                                        <option value="{{ $ch->value }}" {{ old('channel') === $ch->value ? 'selected' : '' }}>
                                            {{ $ch->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('channel')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="row">
                        <div class="col-12 text-end">
                            <a href="{{ route('complaints.index') }}" class="btn btn-secondary me-2">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-success"
                                :disabled="isSensitive">
                                <i data-feather="save" class="me-1" style="width:15px;height:15px;"></i>Enregistrer la plainte
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function complaintForm() {
            return {
                isAnonymous: {{ old('is_anonymous') ? 'true' : 'false' }},
                isSensitive: false,

                init() {
                    // Native Bootstrap selects - no JS init needed
                },

                onTypeChange(typeId) {
                    const select = document.getElementById('complaint_type_id');
                    const opt = select.querySelector(`option[value="${typeId}"]`);
                    this.isSensitive = opt?.dataset?.sensitive === 'true';
                },
            };
        }
    </script>
    @endpush

</x-layout.app>