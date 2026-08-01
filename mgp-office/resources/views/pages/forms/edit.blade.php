<x-layout.app 
    title="Modifier formulaire" 
    description="Mettre à jour le formulaire ODK"
    icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('forms.index') }}">Formulaires ODK</a></li>
        <li class="breadcrumb-item"><a href="{{ route('forms.show', $form) }}">{{ $form->form_id }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-lg-12">
            <div class="card" style="border-radius:8px;">
                
                <div class="card-body">
                    <form method="POST" action="{{ route('forms.update', $form) }}">
                        @csrf @method('PUT')

                        <div class="row mb-4">
                            <div class="col-12 col-md-3">
                                <p class="text-muted small"><strong>ID ODK :</strong> <code>{{ $form->form_id }}</code></p>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="form_type" class="form-label fw-semibold">Type MGP <span class="text-danger">*</span></label>
                                    <select id="form_type" name="form_type"
                                            class="form-select @error('form_type') is-invalid @enderror"
                                            required>
                                        <option value="F01" {{ old('form_type', $form->form_type) === 'F01' ? 'selected' : '' }}>F01 — Dépôt de plainte</option>
                                        <option value="F02" {{ old('form_type', $form->form_type) === 'F02' ? 'selected' : '' }}>F02 — Plainte non sensible</option>
                                        <option value="F03" {{ old('form_type', $form->form_type) === 'F03' ? 'selected' : '' }}>F03 — Rapport VBG/EAS/HS</option>
                                        <option value="F04" {{ old('form_type', $form->form_type) === 'F04' ? 'selected' : '' }}>F04 — PV de conciliation</option>
                                        <option value="F05" {{ old('form_type', $form->form_type) === 'F05' ? 'selected' : '' }}>F05 — Suivi périodique</option>
                                        <option value="F06" {{ old('form_type', $form->form_type) === 'F06' ? 'selected' : '' }}>F06 — PV installation comité</option>
                                        <option value="F07" {{ old('form_type', $form->form_type) === 'F07' ? 'selected' : '' }}>F07 — Évaluation trimestrielle</option>
                                    </select>
                                    @error('form_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-5">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom du formulaire *</label>
                                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name', $form->name) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4">{{ old('description', $form->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" id="is_active" name="is_active" class="form-check-input" value="1"
                                       {{ old('is_active', $form->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Formulaire actif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('forms.show', $form) }}" class="btn btn-secondary btn-sm">
                                <i data-feather="x" class="me-1" style="width:14px;height:14px;"></i> Annuler
                            </a>
                            <button type="submit" class="btn btn-success btn-sm">
                                <i data-feather="save" class="me-1" style="width:14px;height:14px;"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
