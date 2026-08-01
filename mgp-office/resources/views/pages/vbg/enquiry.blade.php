<x-layout.app :title="'Commission d\'enquête - ' . $report->code">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.index') }}">VBG</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.show', $report) }}">{{ $report->code }}</a></li>
        <li class="breadcrumb-item active">Commission d'enquête</li>
    </x-slot>

    <div class="row" style="margin-top:24px;margin-bottom:16px;">
        <div class="col-12 col-md-12 offset-md-12 col-lg-12 offset-lg-12">

            <div class="card" style="border-radius:8px;margin-bottom:0;background:#6f42c1;color:white;">
                <div class="card-body" style="padding:16px 24px;">
                    <div style="font-size:0.8rem;opacity:0.8;">
                        Commission d'enquête - Délai max 30 jours
                    </div>
                    <h5 style="margin:4px 0;">{{ $report->code }}</h5>
                    <div style="opacity:0.85;font-size:0.85rem;">
                        La commission vérifie le lien entre l'incident et le projet PACIPA.
                        Elle ne détermine PAS la culpabilité.
                    </div>
                </div>
            </div>

            <div class="alert alert-danger mt-2" style="font-size:0.85rem;">
                <i data-feather="alert-triangle" style="width:16px;height:16px;" class="me-2"></i>
                <strong>Rappel :</strong> Cette commission vérifie uniquement le lien entre l'incident et le projet.
                Elle ne remplace PAS la procédure judiciaire et n'implique PAS la notion de culpabilité.
                La Banque mondiale sera notifiée (PEES - délai 48h).
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

            <form method="POST" action="{{ route('vbg.enquiry.store', $report) }}" style="margin-top:16px;">
                @csrf

                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="findings" class="form-label">Éléments d'investigation préliminaires (optionnel à ce stade)</label>
                            <textarea id="findings" name="findings" class="form-control" rows="4"
                                      maxlength="3000">{{ old('findings') }}</textarea>
                            @error('findings')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="recommendations" class="form-label">Recommandations initiales (optionnel)</label>
                            <textarea id="recommendations" name="recommendations" class="form-control" rows="4"
                                      maxlength="2000">{{ old('recommendations') }}</textarea>
                            @error('recommendations')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('vbg.show', $report) }}" class="btn btn-secondary">
                        Annuler
                    </a>
                    <button type="submit" class="btn" style="background:#6f42c1;color:white;">
                        <i data-feather="search" style="width:16px;height:16px;"></i> Ouvrir la commission d'enquête
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layout.app>
