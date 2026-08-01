<x-layout.app title="Nouveau signalement VBG"
description="Signalement VBG/EAS/HS - Formulaire F03 (Annexe 9) / La Partie 2 (détails des faits) ne s'affiche qu'après confirmation du consentement éclairé de la survivante."
icon="shield">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.index') }}">VBG</a></li>
        <li class="breadcrumb-item active">Nouveau signalement</li>
    </x-slot>
    
    <div x-data="vbgForm()" x-init="init()" style="margin-top:24px;margin-bottom:16px;">
        <div class="row g-3">
            <div class="col-12 col-lg-12 offset-lg-12">

                {{-- Bannière consentement obligatoire --}}
                <x-vbg.consent-banner />

                
                @if($errors->any())
                <div class="alert alert-danger" role="alert">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('vbg.store') }}">
                    @csrf

                    {{-- PARTIE 1 - Toujours visible --}}
                    <div class="card" style="border-radius:8px;border-left:4px solid #DC3545;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3 text-muted">
                                <span class="badge rounded-circle me-2" style="background:#DC3545;">1</span>
                                Identification (confidentielle)
                            </h5>
                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="channel" class="form-label">Canal de réception *</label>
                                        <select name="channel" id="channel" required class="form-select {{ $errors->has('channel') ? 'is-invalid' : '' }}">
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
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label for="survivor_gender" class="form-label">Genre survivant(e) *</label>
                                        <select name="survivor_gender" id="survivor_gender" required class="form-select {{ $errors->has('survivor_gender') ? 'is-invalid' : '' }}">
                                            <option value="" disabled selected>--</option>
                                            <option value="F" {{ old('survivor_gender') === 'F' ? 'selected' : '' }}>Femme / Fille</option>
                                            <option value="M" {{ old('survivor_gender') === 'M' ? 'selected' : '' }}>Homme / Garçon</option>
                                        </select>
                                        @error('survivor_gender')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="mb-3">
                                        <label for="survivor_age_group" class="form-label">Tranche d'âge *</label>
                                        <select name="survivor_age_group" id="survivor_age_group" required class="form-select {{ $errors->has('survivor_age_group') ? 'is-invalid' : '' }}">
                                            <option value="" disabled selected>-- Âge --</option>
                                            @foreach($ageGroups as $value => $label)
                                            <option value="{{ $value }}" {{ old('survivor_age_group') === $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('survivor_age_group')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="is_anonymous" value="1"
                                               id="is_anonymous" {{ old('is_anonymous') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_anonymous">Signalement anonyme</label>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="caller_phone" class="form-label">Téléphone (optionnel - sera chiffré)</label>
                                        <input id="caller_phone" name="caller_phone" type="tel" maxlength="20"
                                               value="{{ old('caller_phone') }}"
                                               placeholder="+227 XX XX XX XX" class="form-control">
                                        <div class="form-text">
                                            <i data-feather="lock" style="width:12px;height:12px;"></i>
                                            Ce numéro sera chiffré et ne sera jamais affiché sans autorisation.
                                        </div>
                                        @error('caller_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            {{-- Site concerné (optionnel) --}}
                            <div style="margin-top:8px;">
                                <p class="text-muted" style="font-size:0.85rem;margin-bottom:4px;">
                                    Site PACIPA concerné (si applicable) :
                                </p>
                                <x-form.cascade-select
                                    :regions="$regions"
                                    :selected="[]"
                                    :showSite="true"
                                    siteNullableLabel="Non lié à un site PACIPA"
                                />
                            </div>
                        </div>
                    </div>

                    {{-- Consentement --}}
                    <div class="card" style="border-radius:8px;border-left:4px solid #F97B3D;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3 text-muted">
                                <i data-feather="user-plus" style="width:16px;height:16px;"></i> Consentement éclairé
                            </h5>
                            <div class="alert alert-warning" style="border-radius:6px;font-size:0.85rem;">
                                La survivante a été informée de ses droits et de l'utilisation de ses données.
                                Elle consent librement à la collecte de ses informations pour recevoir une aide.
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="consent_given" value="1"
                                       id="consent_given"
                                       x-model="consentGiven"
                                       {{ old('consent_given') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-warning" for="consent_given">
                                    La survivante a donné son consentement éclairé
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- PARTIE 2 - Conditionnelle au consentement --}}
                    <div x-show="consentGiven" x-transition>
                        <div class="card" style="border-radius:8px;border-left:4px solid #1b5e20;">
                            <div class="card-body">
                                <h5 class="fw-semibold mb-3 text-muted">
                                    <span class="badge rounded-circle me-2" style="background:#009A44;">2</span>
                                    Détails de l'incident (consentement requis)
                                </h5>
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="incident_date" class="form-label">Date de l'incident (approx.)</label>
                                            <input id="incident_date" name="incident_date" type="date"
                                                   value="{{ old('incident_date') }}"
                                                   max="{{ date('Y-m-d') }}" class="form-control">
                                            @error('incident_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="incident_zone" class="form-label">Lieu de l'incident (zone générale)</label>
                                            <input id="incident_zone" name="incident_zone" type="text" maxlength="300"
                                                   value="{{ old('incident_zone') }}" class="form-control">
                                            @error('incident_zone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="author_link" class="form-label">Lien de l'auteur présumé avec le projet</label>
                                            <select name="author_link" id="author_link" class="form-select">
                                                <option value="">-- Lien auteur présumé --</option>
                                                @foreach($authorLinks as $al)
                                                <option value="{{ $al->value }}" {{ old('author_link') === $al->value ? 'selected' : '' }}>
                                                    {{ $al->label() }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('author_link')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="classification" class="form-label">Classification GBVIMS</label>
                                            <select name="classification" id="classification" class="form-select">
                                                <option value="">-- Classification GBVIMS --</option>
                                                @foreach($classifications as $cl)
                                                <option value="{{ $cl->value }}" {{ old('classification') === $cl->value ? 'selected' : '' }}>
                                                    {{ $cl->label() }}
                                                </option>
                                                @endforeach
                                            </select>
                                            @error('classification')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('vbg.index') }}" class="btn btn-secondary">
                            Annuler
                        </a>
                        <button type="submit" class="btn btn-danger">
                            <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer le signalement
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function vbgForm() {
        return {
            consentGiven: {{ old('consent_given') ? 'true' : 'false' }},
            init() {
                this.$watch('consentGiven', () => {});
            },
        };
    }
    </script>
    @endpush

</x-layout.app>
