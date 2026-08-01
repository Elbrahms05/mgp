<x-layout.app
    title="Modifier une entreprise"
    description="Mettre à jour les informations"
    icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('companies.index') }}">Entreprises</a></li>
        <li class="breadcrumb-item active">{{ $company->name }}</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-lg-12 offset-lg-12">
            <div class="card">
                
                <div class="card-body">
                    <form method="POST" action="{{ route('companies.update', $company) }}">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="name" class="form-label">Nom de l'entreprise *</label>
                                <input type="text" id="name" name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $company->name) }}" required>
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="sector" class="form-label">Secteur d'activité</label>
                                <input type="text" id="sector" name="sector"
                                       class="form-control"
                                       value="{{ old('sector', $company->sector) }}"
                                       placeholder="Ex: Agriculture">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="number_registre" class="form-label">N° RCCM / Enregistrement</label>
                                <input type="text" id="number_registre" name="number_registre"
                                       class="form-control @error('number_registre') is-invalid @enderror"
                                       value="{{ old('number_registre', $company->number_registre) }}">
                                @error('number_registre')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email"
                                       class="form-control @error('email') is-invalid @enderror"
                                       value="{{ old('email', $company->email) }}">
                                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="phone" class="form-label">Téléphone</label>
                                <input type="tel" id="phone" name="phone"
                                       class="form-control"
                                       value="{{ old('phone', $company->phone) }}">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="website" class="form-label">Site web</label>
                                <input type="url" id="website" name="website"
                                       class="form-control @error('website') is-invalid @enderror"
                                       value="{{ old('website', $company->website) }}"
                                       placeholder="https://...">
                                @error('website')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <label for="address" class="form-label">Adresse</label>
                                <textarea id="address" name="address" class="form-control" rows="3">{{ old('address', $company->address) }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                                           value="1" {{ old('is_active', $company->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Entreprise active</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('companies.show', $company) }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save" style="width:16px;height:16px;" class="me-1"></i>Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
