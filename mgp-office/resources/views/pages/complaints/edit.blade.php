<x-layout.app :title="'Modifier - ' . ($complaint->registration_number ?? '#'.$complaint->id)"
icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.show', $complaint) }}">{{ $complaint->registration_number }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div x-data="{ isAnonymous: {{ $complaint->is_anonymous ? 'true' : 'false' }} }" style="margin-top:24px;margin-bottom:16px;">
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

                <form method="POST" action="{{ route('complaints.update', $complaint) }}">
                    @csrf @method('PUT')

                    {{-- Section : Type et canal --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <i data-feather="info" class="me-1" style="width:16px;height:16px;"></i>Classification
                            </h5>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="complaint_type_id" class="form-label">Type de plainte *</label>
                                        <select name="complaint_type_id" id="complaint_type_id"
                                                class="form-select {{ $errors->has('complaint_type_id') ? 'is-invalid' : '' }}" required>
                                            @foreach($complaintTypes as $ct)
                                            <option value="{{ $ct->id }}"
                                                {{ old('complaint_type_id', $complaint->complaint_type_id) == $ct->id ? 'selected' : '' }}>
                                                {{ $ct->sort_order }}. {{ $ct->label }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('complaint_type_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="channel" class="form-label">Canal de réception *</label>
                                        <select name="channel" id="channel"
                                                class="form-select {{ $errors->has('channel') ? 'is-invalid' : '' }}" required>
                                            @foreach($channels as $ch)
                                            <option value="{{ $ch->value }}"
                                                {{ old('channel', $complaint->channel?->value) === $ch->value ? 'selected' : '' }}>
                                                {{ $ch->label() }}
                                            </option>
                                            @endforeach
                                        </select>
                                        @error('channel')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section : Identité plaignant --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <i data-feather="user" class="me-1" style="width:16px;height:16px;"></i>Identité du plaignant
                            </h5>
                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_anonymous" value="1"
                                           id="is_anonymous"
                                           x-model="isAnonymous"
                                           {{ old('is_anonymous', $complaint->is_anonymous) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_anonymous">Plainte anonyme</label>
                                </div>
                            </div>
                            <div x-show="!isAnonymous">
                                <div class="row">
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="complainant_name" class="form-label">Nom complet</label>
                                            <input id="complainant_name" name="complainant_name" type="text" maxlength="200"
                                                   value="{{ old('complainant_name', $complaint->complainant_name) }}"
                                                   class="form-control {{ $errors->has('complainant_name') ? 'is-invalid' : '' }}">
                                            @error('complainant_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="complainant_age" class="form-label">Âge</label>
                                            <input id="complainant_age" name="complainant_age" type="number" min="0" max="120"
                                                   value="{{ old('complainant_age', $complaint->complainant_age) }}"
                                                   class="form-control {{ $errors->has('complainant_age') ? 'is-invalid' : '' }}">
                                            @error('complainant_age')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                    <div class="col-6 col-md-3">
                                        <div class="mb-3">
                                            <label for="complainant_gender" class="form-label">Genre</label>
                                            <select name="complainant_gender" id="complainant_gender" class="form-select">
                                                <option value="">-</option>
                                                <option value="M" {{ old('complainant_gender', $complaint->complainant_gender) === 'M' ? 'selected' : '' }}>Homme</option>
                                                <option value="F" {{ old('complainant_gender', $complaint->complainant_gender) === 'F' ? 'selected' : '' }}>Femme</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="mb-3">
                                            <label for="complainant_phone" class="form-label">Téléphone</label>
                                            <input id="complainant_phone" name="complainant_phone" type="tel" maxlength="20"
                                                   value="{{ old('complainant_phone', $complaint->complainant_phone) }}"
                                                   class="form-control {{ $errors->has('complainant_phone') ? 'is-invalid' : '' }}">
                                            @error('complainant_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Section : Objet --}}
                    <div class="card" style="border-radius:8px;">
                        <div class="card-body">
                            <h5 class="fw-semibold mb-3">
                                <i data-feather="file-text" class="me-1" style="width:16px;height:16px;"></i>Objet de la plainte
                            </h5>
                            <div class="row">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="incident_date" class="form-label">Date des faits *</label>
                                        <input id="incident_date" name="incident_date" type="date"
                                               value="{{ old('incident_date', $complaint->incident_date?->format('Y-m-d')) }}"
                                               max="{{ date('Y-m-d') }}"
                                               class="form-control {{ $errors->has('incident_date') ? 'is-invalid' : '' }}" required>
                                        @error('incident_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="subject" class="form-label">Objet *</label>
                                        <input id="subject" name="subject" type="text" maxlength="300"
                                               value="{{ old('subject', $complaint->subject) }}"
                                               class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}" required>
                                        @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description *</label>
                                        <textarea id="description" name="description"
                                                  class="form-control {{ $errors->has('description') ? 'is-invalid' : '' }}"
                                                  minlength="20" rows="5"
                                                  required>{{ old('description', $complaint->description) }}</textarea>
                                        @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="accused_party" class="form-label">Partie mise en cause</label>
                                        <input id="accused_party" name="accused_party" type="text" maxlength="300"
                                               value="{{ old('accused_party', $complaint->accused_party) }}"
                                               class="form-control {{ $errors->has('accused_party') ? 'is-invalid' : '' }}">
                                        @error('accused_party')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="row">
                        <div class="col-12 text-end">
                            <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-secondary me-2">
                                Annuler
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save" class="me-1" style="width:15px;height:15px;"></i>Enregistrer les modifications
                            </button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </div>

</x-layout.app>
