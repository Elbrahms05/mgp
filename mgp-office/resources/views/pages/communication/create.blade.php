<x-layout.app
    title="Nouvelle campagne"
    description="Planifier une nouvelle campagne de sensibilisation"
    icon="megaphone">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('communication.index') }}">Communication</a></li>
        <li class="breadcrumb-item active">Nouvelle campagne</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-md-12 offset-md-12">
            <div class="card">
                
                <div class="card-body">
                    <form method="POST" action="{{ route('communication.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="title" class="form-label">Titre de la campagne *</label>
                                <input type="text" id="title" name="title" class="form-control @error('title') is-invalid @enderror"
                                       value="{{ old('title') }}" required>
                                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="type" class="form-label">Type *</label>
                                <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($campaignTypes as $key => $label)
                                    <option value="{{ $key }}" {{ old('type') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description / Objectifs *</label>
                                <textarea id="description" name="description" class="form-control @error('description') is-invalid @enderror"
                                          style="min-height:120px;" required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="target_audience" class="form-label">Public cible</label>
                                <input type="text" id="target_audience" name="target_audience" class="form-control"
                                       value="{{ old('target_audience') }}">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Régions ciblées</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($regions as $region)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="region_{{ $region->id }}"
                                               name="regions[]" value="{{ $region->name }}"
                                               {{ in_array($region->name, old('regions', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="region_{{ $region->id }}">{{ $region->name }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Langues</label>
                                <div class="d-flex flex-wrap gap-3">
                                    @foreach($languages as $code => $label)
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input" id="lang_{{ $code }}"
                                               name="languages[]" value="{{ $code }}"
                                               {{ in_array($code, old('languages', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lang_{{ $code }}">{{ $label }}</label>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="start_date" class="form-label">Date début *</label>
                                <input type="date" id="start_date" name="start_date" class="form-control"
                                       value="{{ old('start_date') }}" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="end_date" class="form-label">Date fin</label>
                                <input type="date" id="end_date" name="end_date" class="form-control"
                                       value="{{ old('end_date') }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="status" class="form-label">Statut *</label>
                                <select name="status" id="status" class="form-select" required>
                                    @foreach(['planned' => 'Planifiée', 'active' => 'En cours', 'completed' => 'Terminée', 'cancelled' => 'Annulée'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('status', 'planned') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('communication.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i data-feather="save" style="width:16px;height:16px;" class="me-1"></i>Créer la campagne
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
