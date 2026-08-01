<x-layout.app
    title="Modifier plainte"
    description="{{ $worker->registration_number }} - informations du travailleur"
    icon="edit-2">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workers.index') }}">Plaintes travailleurs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('workers.show', $worker) }}">{{ $worker->registration_number }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-lg-12 offset-lg-12 ">
            <div class="card mb-4">
                
                <div class="card-body">

                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('workers.update', $worker) }}"
                          x-data="{ workerType: '{{ old('worker_type', $worker->worker_type) }}' }">
                        @csrf @method('PUT')

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="worker_name" class="form-label">Nom du travailleur <span class="text-danger">*</span></label>
                                <input type="text" id="worker_name" name="worker_name"
                                       value="{{ old('worker_name', $worker->worker_name) }}"
                                       required class="form-control @error('worker_name') is-invalid @enderror">
                                @error('worker_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="worker_phone" class="form-label">Téléphone</label>
                                <input type="tel" id="worker_phone" name="worker_phone"
                                       value="{{ old('worker_phone', $worker->worker_phone) }}"
                                       class="form-control @error('worker_phone') is-invalid @enderror">
                                @error('worker_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="incident_date" class="form-label">Date du fait <span class="text-danger">*</span></label>
                                <input type="date" id="incident_date" name="incident_date"
                                       value="{{ old('incident_date', $worker->incident_date?->format('Y-m-d')) }}"
                                       required class="form-control @error('incident_date') is-invalid @enderror">
                                @error('incident_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de travailleur</label>
                                <input type="text" class="form-control" disabled
                                       value="{{ $worker->worker_type === 'direct' ? 'Direct UGP' : 'Contractuel' }}">
                                <div class="form-text text-muted">Non modifiable après création.</div>
                            </div>
                            <div class="col-12">
                                <label for="subject" class="form-label">Objet de la plainte <span class="text-danger">*</span></label>
                                <input type="text" id="subject" name="subject"
                                       value="{{ old('subject', $worker->subject) }}"
                                       required class="form-control @error('subject') is-invalid @enderror">
                                @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-12">
                                <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea id="description" name="description"
                                          rows="6" required
                                          class="form-control @error('description') is-invalid @enderror">{{ old('description', $worker->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="alert alert-info py-2 mt-3 mb-0">
                            <i data-feather="info" style="width:14px;height:14px;" class="me-1"></i>
                            Le statut et le niveau se gèrent via les <strong>Actions</strong> dans la fiche plainte.
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('workers.show', $worker) }}" class="btn btn-secondary">Annuler</a>
                            <button type="submit" class="btn btn-primary">
                                <i data-feather="save" style="width:16px;height:16px;" class="me-1"></i>
                                Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
