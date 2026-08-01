<x-layout.app :title="'Modifier - ' . $site->name" 
description="Modifier le site "
icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sites.index') }}">Sites</a></li>
        <li class="breadcrumb-item"><a href="{{ route('sites.show', $site) }}">{{ $site->name }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div class="row margin-row">
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

            <form method="POST" action="{{ route('sites.update', $site) }}">
                @csrf @method('PUT')

                {{-- Bloc 1 : Identification --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="info" style="width:16px;height:16px;"></i> Identification du site
                        </h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="code" class="form-label">Code site *</label>
                                    <input id="code" name="code" type="text" maxlength="30"
                                           value="{{ old('code', $site->code) }}"
                                           class="form-control {{ $errors->has('code') ? 'is-invalid' : '' }}" required>
                                    @error('code')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-8">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nom du site *</label>
                                    <input id="name" name="name" type="text" maxlength="200"
                                           value="{{ old('name', $site->name) }}"
                                           class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                                    @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="site_type_id" class="form-label">Type de site *</label>
                                    <select name="site_type_id" id="site_type_id" required class="form-select {{ $errors->has('site_type_id') ? 'is-invalid' : '' }}">
                                        <option value="" disabled>-- Choisir un type --</option>
                                        @foreach($siteTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('site_type_id', $site->site_type_id) == $type->id ? 'selected' : '' }}>
                                            {{ $type->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('site_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Composante PACIPA *</label>
                                    <select name="category" id="category" required class="form-select {{ $errors->has('category') ? 'is-invalid' : '' }}">
                                        <option value="" disabled>-- Choisir une composante --</option>
                                        @foreach($categories as $cat)
                                        <option value="{{ $cat->value }}"
                                            {{ old('category', $site->category->value) == $cat->value ? 'selected' : '' }}>
                                            {{ $cat->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('category')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 2 : Géographie --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="map-pin" style="width:16px;height:16px;"></i> Localisation géographique
                        </h5>
                        <x-form.cascade-select
                            :regions="$regions"
                            :selected="$selected"
                            :showSite="false"
                            :required="true"
                        />
                        <div class="row g-3 mt-2">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="latitude" class="form-label">Latitude GPS (optionnel)</label>
                                    <input id="latitude" name="latitude" type="number" step="any"
                                           value="{{ old('latitude', $site->latitude) }}"
                                           class="form-control {{ $errors->has('latitude') ? 'is-invalid' : '' }}">
                                    @error('latitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="longitude" class="form-label">Longitude GPS (optionnel)</label>
                                    <input id="longitude" name="longitude" type="number" step="any"
                                           value="{{ old('longitude', $site->longitude) }}"
                                           class="form-control {{ $errors->has('longitude') ? 'is-invalid' : '' }}">
                                    @error('longitude')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 3 : Caractéristiques --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="bar-chart-2" style="width:16px;height:16px;"></i> Caractéristiques du site
                        </h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="area_hectares" class="form-label">Superficie (hectares)</label>
                                    <input id="area_hectares" name="area_hectares" type="number" step="0.0001" min="0"
                                           value="{{ old('area_hectares', $site->area_hectares) }}"
                                           class="form-control {{ $errors->has('area_hectares') ? 'is-invalid' : '' }}">
                                    @error('area_hectares')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="beneficiaries_count" class="form-label">Nombre de bénéficiaires</label>
                                    <input id="beneficiaries_count" name="beneficiaries_count" type="number" min="0"
                                           value="{{ old('beneficiaries_count', $site->beneficiaries_count) }}"
                                           class="form-control {{ $errors->has('beneficiaries_count') ? 'is-invalid' : '' }}">
                                    @error('beneficiaries_count')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control" rows="4"
                                      maxlength="2000">{{ old('description', $site->description) }}</textarea>
                            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Bloc 4 : Calendrier & Statut --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="calendar" style="width:16px;height:16px;"></i> Calendrier et statut
                        </h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="start_date" class="form-label">Date de début *</label>
                                    <input id="start_date" name="start_date" type="date"
                                           value="{{ old('start_date', $site->start_date?->format('Y-m-d')) }}"
                                           class="form-control {{ $errors->has('start_date') ? 'is-invalid' : '' }}" required>
                                    @error('start_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="expected_end_date" class="form-label">Date de fin prévue *</label>
                                    <input id="expected_end_date" name="expected_end_date" type="date"
                                           value="{{ old('expected_end_date', $site->expected_end_date?->format('Y-m-d')) }}"
                                           class="form-control {{ $errors->has('expected_end_date') ? 'is-invalid' : '' }}" required>
                                    @error('expected_end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="mb-3">
                                    <label for="actual_end_date" class="form-label">Date de fin réelle</label>
                                    <input id="actual_end_date" name="actual_end_date" type="date"
                                           value="{{ old('actual_end_date', $site->actual_end_date?->format('Y-m-d')) }}"
                                           class="form-control {{ $errors->has('actual_end_date') ? 'is-invalid' : '' }}">
                                    @error('actual_end_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Statut *</label>
                                    <select name="status" id="status" required class="form-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                                        @foreach($statuses as $s)
                                        <option value="{{ $s->value }}"
                                            {{ old('status', $site->status->value) === $s->value ? 'selected' : '' }}>
                                            {{ $s->label() }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="responsible_user_id" class="form-label">Responsable UGP</label>
                                    <select name="responsible_user_id" id="responsible_user_id" class="form-select">
                                        <option value="">-- Sélectionner un responsable --</option>
                                        @foreach($users as $user)
                                        <option value="{{ $user->id }}"
                                            {{ old('responsible_user_id', $site->responsible_user_id) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('responsible_user_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('sites.show', $site) }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-primary">
                        <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer les modifications
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layout.app>
