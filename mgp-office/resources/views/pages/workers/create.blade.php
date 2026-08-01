<x-layout.app title="Nouvelle plainte travailleur">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Plaintes travailleurs</a></li>
        <li class="breadcrumb-item active">Nouvelle</li>
    </x-slot>

    <div x-data="{ workerType: '{{ old('worker_type', '') }}' }" style="margin-top:24px;margin-bottom:16px;">
        <div class="row">
            <div class="col-12 col-md-12 offset-md-12">

                {{-- Délais SLA info --}}
                <div class="alert alert-info" style="border-radius:8px;margin-bottom:16px;">
                    <i data-feather="clock" style="width:16px;height:16px;" class="me-2"></i>
                    <strong>Délais SLA :</strong>
                    Travailleurs directs : N1 = 7 jours / N2 = 14 jours.
                    Travailleurs contractuels : N1 = 7 jours ouvrables / N2 = 7 jours.
                </div>

                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="tool" style="width:20px;height:20px;"></i> Enregistrer une plainte travailleur
                        </h5>

                        <form method="POST" action="{{ route('workers.store') }}">
                            @csrf

                            @if($errors->any())
                            <div class="alert alert-danger mb-3">
                                <ul style="margin:0;padding-left:20px;">
                                    @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif

                            <div class="row g-3 mt-2">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="worker_type" class="form-label">Type de travailleur *</label>
                                        <select name="worker_type" id="worker_type" required
                                                @change="workerType = $event.target.value"
                                                class="form-select {{ $errors->has('worker_type') ? 'is-invalid' : '' }}">
                                            <option value="">-- Sélectionner --</option>
                                            <option value="direct" {{ old('worker_type') === 'direct' ? 'selected' : '' }}>
                                                Travailleur direct (UGP/UCR)
                                            </option>
                                            <option value="contractor" {{ old('worker_type') === 'contractor' ? 'selected' : '' }}>
                                                Travailleur contractuel (entreprise)
                                            </option>
                                        </select>
                                        @error('worker_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="incident_date" class="form-label">Date du fait *</label>
                                        <input type="date" id="incident_date" name="incident_date"
                                               value="{{ old('incident_date') }}" required
                                               class="form-control {{ $errors->has('incident_date') ? 'is-invalid' : '' }}">
                                        @error('incident_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="worker_name" class="form-label">Nom du travailleur *</label>
                                        <input type="text" id="worker_name" name="worker_name"
                                               value="{{ old('worker_name') }}" required
                                               class="form-control {{ $errors->has('worker_name') ? 'is-invalid' : '' }}">
                                        @error('worker_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="worker_phone" class="form-label">Téléphone du travailleur</label>
                                        <input type="tel" id="worker_phone" name="worker_phone"
                                               value="{{ old('worker_phone') }}"
                                               class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="employer_name" class="form-label">Employeur / Entreprise *</label>
                                
                                <!-- Contracteur: Select from Company model -->
                                <div x-show="workerType === 'contractor'" x-transition>
                                    <select id="employer_name" name="company_id" class="form-select {{ $errors->has('company_id') ? 'is-invalid' : '' }}"
                                            :required="workerType === 'contractor'">
                                        <option value="">-- Sélectionner une entreprise --</option>
                                        @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }} ({{ $company->sector ?? 'N/A' }})
                                        </option>
                                        @endforeach
                                    </select>
                                    @error('company_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                                
                                <!-- Direct worker: Disabled text field with 'Projet' -->
                                <div x-show="workerType === 'direct'" x-transition>
                                    <input type="text" id="employer_direct" class="form-control" disabled
                                           value="Projet" placeholder="Projet">
                                    <input type="hidden" name="employer_name" value="Projet">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="subject" class="form-label">Objet de la plainte *</label>
                                <input type="text" id="subject" name="subject"
                                       value="{{ old('subject') }}" required
                                       class="form-control {{ $errors->has('subject') ? 'is-invalid' : '' }}">
                                @error('subject')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description détaillée *</label>
                                <textarea id="description" name="description" class="form-control" rows="5"
                                          required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-3">
                                <a href="{{ route('workers.index') }}" class="btn btn-secondary">Annuler</a>
                                <button type="submit" class="btn btn-success">
                                    <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
