<x-layout.app
    title="Modifier guide contextuel"
    description="{{ $guide->page_title }}"
    icon="book-open">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('documentation.index') }}">Documentation</a></li>
        <li class="breadcrumb-item active"><code>{{ $guide->route_name }}</code></li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-md-12 offset-md-12">
            <div class="card">
                
                <div class="card-body">
                    <form method="POST" action="{{ route('documentation.update', $guide) }}">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="route_name" class="form-label">Nom de route *</label>
                                <input type="text" id="route_name" name="route_name"
                                       class="form-control @error('route_name') is-invalid @enderror"
                                       value="{{ old('route_name', $guide->route_name) }}" required>
                                @error('route_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="page_title" class="form-label">Titre de page *</label>
                                <input type="text" id="page_title" name="page_title"
                                       class="form-control @error('page_title') is-invalid @enderror"
                                       value="{{ old('page_title', $guide->page_title) }}" required>
                                @error('page_title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-8">
                                <label for="mgp_reference" class="form-label">Référence MGP *</label>
                                <input type="text" id="mgp_reference" name="mgp_reference"
                                       class="form-control @error('mgp_reference') is-invalid @enderror"
                                       value="{{ old('mgp_reference', $guide->mgp_reference) }}" required>
                                @error('mgp_reference')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12 col-md-4">
                                <label for="display_mode" class="form-label">Mode *</label>
                                <select name="display_mode" id="display_mode" class="form-select" required>
                                    @foreach(['sidebar' => 'Sidebar', 'modal' => 'Modal', 'tooltip' => 'Tooltip'] as $val => $lbl)
                                    <option value="{{ $val }}" {{ old('display_mode', $guide->display_mode) === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label for="summary" class="form-label">Résumé *</label>
                                <textarea id="summary" name="summary"
                                          class="form-control @error('summary') is-invalid @enderror"
                                          required>{{ old('summary', $guide->summary) }}</textarea>
                                @error('summary')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-12">
                                <label for="detailed_guide" class="form-label">
                                    Guide détaillé <span class="text-muted">(Markdown)</span>
                                </label>
                                <textarea id="detailed_guide" name="detailed_guide" class="form-control"
                                          style="min-height:200px;">{{ old('detailed_guide', $guide->detailed_guide) }}</textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" id="is_active" name="is_active" class="form-check-input"
                                           value="1" {{ old('is_active', $guide->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">Guide actif</label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <a href="{{ route('documentation.index') }}" class="btn btn-outline-secondary">Annuler</a>
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
