<x-layout.app title="Saisie différée — fiche papier"
    description="Reprise d'une fiche F01 papier reçue antérieurement"
    icon="file-plus">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item active">Saisie différée</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-9">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-1"></i>
                La <strong>date de réception</strong> saisie ci-dessous sert de point de départ au calcul
                des délais (SLA). Joignez la fiche papier numérisée comme preuve.
            </div>

            <div class="card mb-4">
                <div class="card-header">Reprise d'une fiche F01 papier</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('complaints.deferred.store') }}" enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Type de plainte <span class="text-danger">*</span></label>
                                <select name="complaint_type_id" class="form-select @error('complaint_type_id') is-invalid @enderror" required>
                                    <option value="">— Choisir —</option>
                                    @foreach ($complaintTypes as $type)
                                        <option value="{{ $type->id }}" @selected(old('complaint_type_id') == $type->id)>{{ $type->label }}</option>
                                    @endforeach
                                </select>
                                @error('complaint_type_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date de réception papier <span class="text-danger">*</span></label>
                                <input type="date" name="received_at" max="{{ now()->format('Y-m-d') }}"
                                       value="{{ old('received_at') }}"
                                       class="form-control @error('received_at') is-invalid @enderror" required>
                                @error('received_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Nom du plaignant</label>
                                <input type="text" name="complainant_name" value="{{ old('complainant_name') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="complainant_phone" value="{{ old('complainant_phone') }}" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Localité</label>
                                <input type="text" name="complainant_locality" value="{{ old('complainant_locality') }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Objet</label>
                                <input type="text" name="subject" value="{{ old('subject') }}" class="form-control">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Description des faits <span class="text-danger">*</span></label>
                                <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror" required>{{ old('description') }}</textarea>
                                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Fiche papier numérisée (PDF/JPG/PNG) <span class="text-danger">*</span></label>
                                <input type="file" name="scan" accept=".pdf,.jpg,.jpeg,.png"
                                       class="form-control @error('scan') is-invalid @enderror" required>
                                @error('scan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input type="checkbox" name="is_anonymous" value="1" class="form-check-input" id="anon"
                                           @checked(old('is_anonymous'))>
                                    <label class="form-check-label" for="anon">Plainte anonyme</label>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">Enregistrer la fiche</button>
                            <a href="{{ route('complaints.index') }}" class="btn btn-link">Annuler</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout.app>
