<x-layout.app :title="'Modifier - ' . $committee->name"
description=" Modifier le comité "
icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Comités</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.show', $committee) }}">{{ $committee->name }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div x-data="committeeEditForm()" x-init="init()" class="margin-row">
        <div class="row g-3">
            <div class="col-12 col-lg-12 offset-lg-12">

               
                @if($errors->any())
                <div class="alert alert-danger">
                    <ul style="margin:0;padding-left:20px;">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('committees.update', $committee) }}">
                    @csrf @method('PUT')

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
                                               value="{{ old('name', $committee->name) }}"
                                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-4">
                                    <div class="mb-3">
                                        <label for="level" class="form-label">Niveau *</label>
                                        <select name="level" id="level" x-model="level" @change="onLevelChange()" required
                                                class="form-select {{ $errors->has('level') ? 'is-invalid' : '' }}">
                                            @foreach($levels as $l)
                                            <option value="{{ $l->value }}"
                                                {{ old('level', $committee->level->value) === $l->value ? 'selected' : '' }}>
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
                                               value="{{ old('installation_date', $committee->installation_date?->format('Y-m-d')) }}"
                                               class="form-control {{ $errors->has('installation_date') ? 'is-invalid' : '' }}" required>
                                        @error('installation_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 d-flex align-items-center" style="padding-top:8px;">
                                    <div class="form-check">
                                        <input type="checkbox" id="is_active" name="is_active" value="1"
                                               class="form-check-input"
                                               {{ old('is_active', $committee->is_active) ? 'checked' : '' }}>
                                        <label for="is_active" class="form-check-label">Comité actif</label>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes</label>
                                <textarea id="notes" name="notes" class="form-control" rows="3"
                                          maxlength="2000">{{ old('notes', $committee->notes) }}</textarea>
                            </div>
                        </div>
                    </div>

                    {{-- Bloc 2 : Localisation --}}
                    <div class="card" style="border-radius:8px;" x-show="level !== 'cngp'">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3 text-muted">
                                <i data-feather="map-pin" style="width:16px;height:16px;"></i> Localisation
                            </h5>
                            <x-form.cascade-select
                                :regions="$regions"
                                :selected="$selected"
                                :showSite="false"
                                :required="false"
                            />
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="d-flex gap-2 justify-content-end">
                        <a href="{{ route('committees.show', $committee) }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-primary">
                            <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function committeeEditForm() {
        return {
            level: '{{ old('level', $committee->level->value) }}',
            init() {},
            onLevelChange() {},
        };
    }
    </script>
    @endpush

</x-layout.app>
