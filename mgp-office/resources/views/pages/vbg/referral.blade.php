<x-layout.app :title="'Référencement - ' . $report->code">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.index') }}">VBG</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.show', $report) }}">{{ $report->code }}</a></li>
        <li class="breadcrumb-item active">Référencement</li>
    </x-slot>

    <div class="row" style="margin-top:24px;margin-bottom:16px;">
        <div class="col-12 col-md-12 offset-md-12 col-lg-12 offset-lg-12">

            <div class="card" style="border-radius:8px;margin-bottom:0;background:#DC3545;color:white;">
                <div class="card-body" style="padding:16px 24px;">
                    <div style="font-size:0.8rem;opacity:0.8;">Référencement vers les services - Délai 72h max</div>
                    <h5 style="margin:4px 0 0;">{{ $report->code }}</h5>
                </div>
            </div>

            <div class="alert alert-warning mt-2">
                <i data-feather="clock" style="width:16px;height:16px;" class="me-2"></i>
                <strong>Délai contractuel : 72 heures</strong> à compter de la réception du signalement.
                Le référencement doit être confirmé et documenté.
            </div>

            @if($errors->any())
            <div class="alert alert-danger">
                <ul style="margin:0;padding-left:20px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('vbg.referral.store', $report) }}" style="margin-top:16px;">
                @csrf

                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="service_type" class="form-label">Type de service *</label>
                            <select name="service_type" id="service_type" required class="form-select {{ $errors->has('service_type') ? 'is-invalid' : '' }}">
                                <option value="" disabled selected>-- Type de service --</option>
                                @foreach($serviceTypes as $st)
                                <option value="{{ $st->value }}" {{ old('service_type') === $st->value ? 'selected' : '' }}>
                                    {{ $st->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('service_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="provider_name" class="form-label">Nom du prestataire / structure *</label>
                            <input id="provider_name" name="provider_name" type="text" maxlength="200"
                                   value="{{ old('provider_name') }}"
                                   class="form-control {{ $errors->has('provider_name') ? 'is-invalid' : '' }}" required>
                            @error('provider_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="provider_contact" class="form-label">Contact prestataire (tél / email)</label>
                            <input id="provider_contact" name="provider_contact" type="text" maxlength="100"
                                   value="{{ old('provider_contact') }}"
                                   class="form-control {{ $errors->has('provider_contact') ? 'is-invalid' : '' }}">
                            @error('provider_contact')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notes complémentaires</label>
                            <textarea id="notes" name="notes" class="form-control" rows="4"
                                      maxlength="1000">{{ old('notes') }}</textarea>
                            @error('notes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('vbg.show', $report) }}" class="btn btn-secondary">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i data-feather="activity" style="width:16px;height:16px;"></i> Enregistrer le référencement
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layout.app>
