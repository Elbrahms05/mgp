<x-layout.app
    title="Nouveau guide contextuel"
    description="Créer un guide d'aide pour une page du back-office"
    icon="book-open">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('documentation.index') }}">Documentation</a></li>
        <li class="breadcrumb-item active">Nouveau guide</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-md-12 offset-md-12">
            <div class="card">
                
                <div class="card-body">
                    <form method="POST" action="{{ route('documentation.store') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="route_name" class="form-label">Nom de route Laravel *</label>
                                <input type="text" id="route_name" name="route_name"
                                       class="form-control @error('route_name') is-invalid @enderror"
                                       value="{{ old('route_name') }}" required
                                       placeholder="ex: complaints.index">
                                @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="page_title" class="form-label">Titre de la page *</label>
                                <input type="text" id="page_title" name="page_title"
                                       class="form-control @error('page_title') is-invalid @enderror"
                                       value="{{ old('page_title') }}" required>
                                @error('page_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="mgp_reference" class="form-label">Référence MGP *</label>
                                <input type="text" id="mgp_reference" name="mgp_reference"
                                       class="form-control @error('mgp_reference') is-invalid @enderror"
                                       value="{{ old('mgp_reference') }}" required
                                       placeholder="ex: §4.2.1, Tableau 4, Annexe 8">
                                @error('mgp_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="display_mode" class="form-label">Mode affichage *</label>
                                <select name="display_mode" id="display_mode" class="form-select" required>
                                    <option value="sidebar" {{ old('display_mode', 'sidebar') === 'sidebar' ? 'selected' : '' }}>Sidebar</option>
                                    <option value="modal" {{ old('display_mode') === 'modal' ? 'selected' : '' }}>Modal</option>
                                    <option value="tooltip" {{ old('display_mode') === 'tooltip' ? 'selected' : '' }}>Tooltip</option>
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="summary" class="form-label">Résumé (objectif page) *</label>
                                <textarea id="summary" name="summary"
                                          class="form-control @error('summary') is-invalid @enderror"
                                          required>{{ old('summary') }}</textarea>
                                @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="detailed_guide" class="form-label">
                                    Guide détaillé <span class="text-muted">(Markdown - étapes, alertes SLA, interdictions)</span>
                                </label>
                                <textarea id="detailed_guide" name="detailed_guide" class="form-control"
                                          style="min-height:200px;">{{ old('detailed_guide') }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                                           value="1" {{ old('is_active', '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Guide actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('documentation.index') }}" class="btn btn-outline-secondary">Annuler</a>
                            <button type="submit" class="btn btn-success">
                                <i data-feather="save" style="width:16px;height:16px;" class="me-1"></i>Créer le guide
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
