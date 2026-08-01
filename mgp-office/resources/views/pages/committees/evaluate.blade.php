<x-layout.app :title="'Évaluation - ' . $committee->name">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.index') }}">Comités</a></li>
        <li class="breadcrumb-item"><a href="{{ route('committees.show', $committee) }}">{{ $committee->name }}</a></li>
        <li class="breadcrumb-item active">Évaluation trimestrielle</li>
    </x-slot>

    <div class="row margin-row">
        <div class="col-12 col-lg-12 offset-lg-12">

            {{-- Header comité --}}
            <div class="card" style="border-radius:8px;margin-bottom:0;background:#009A44;color:white;">
                <div class="card-body" style="padding:16px 24px;">
                    <div style="font-size:0.8rem;opacity:0.8;text-transform:uppercase;">
                        Évaluation trimestrielle - Formulaire F07 (Annexe 10)
                    </div>
                    <h5 style="margin:4px 0 0;">{{ $committee->name }}</h5>
                    <div style="opacity:0.85;font-size:0.85rem;">{{ $committee->level->label() }}</div>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-danger mt-3">
                <ul style="margin:0;padding-left:20px;">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('committees.evaluate.store', $committee) }}" style="margin-top:16px;">
                @csrf

                {{-- Bloc 1 : Période --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="calendar" style="width:16px;height:16px;"></i> Période d'évaluation
                        </h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="period_start" class="form-label">Début de période *</label>
                                    <input id="period_start" name="period_start" type="date"
                                           value="{{ old('period_start') }}"
                                           class="form-control {{ $errors->has('period_start') ? 'is-invalid' : '' }}" required>
                                    @error('period_start')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="period_end" class="form-label">Fin de période *</label>
                                    <input id="period_end" name="period_end" type="date"
                                           value="{{ old('period_end') }}"
                                           class="form-control {{ $errors->has('period_end') ? 'is-invalid' : '' }}" required>
                                    @error('period_end')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 2 : Membres --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="users" style="width:16px;height:16px;"></i> État des membres
                        </h5>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="members_total" class="form-label">Nombre total de membres *</label>
                                    <input id="members_total" name="members_total" type="number" min="0"
                                           value="{{ old('members_total', $committee->members->count()) }}"
                                           class="form-control {{ $errors->has('members_total') ? 'is-invalid' : '' }}" required>
                                    @error('members_total')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="mb-3">
                                    <label for="members_operational" class="form-label">Membres opérationnels (présents ce trimestre) *</label>
                                    <input id="members_operational" name="members_operational" type="number" min="0"
                                           value="{{ old('members_operational', $committee->members->where('is_active', true)->count()) }}"
                                           class="form-control {{ $errors->has('members_operational') ? 'is-invalid' : '' }}" required>
                                    @error('members_operational')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 3 : Activités plaintes --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="bar-chart-2" style="width:16px;height:16px;"></i> Activités de gestion des plaintes
                        </h5>
                        <div class="row g-3">
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label for="complaints_registered" class="form-label">Plaintes reçues *</label>
                                    <input id="complaints_registered" name="complaints_registered" type="number" min="0"
                                           value="{{ old('complaints_registered', 0) }}"
                                           class="form-control {{ $errors->has('complaints_registered') ? 'is-invalid' : '' }}" required>
                                    @error('complaints_registered')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label for="complaints_processed" class="form-label">Plaintes traitées *</label>
                                    <input id="complaints_processed" name="complaints_processed" type="number" min="0"
                                           value="{{ old('complaints_processed', 0) }}"
                                           class="form-control {{ $errors->has('complaints_processed') ? 'is-invalid' : '' }}" required>
                                    @error('complaints_processed')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label for="complaints_resolved_local" class="form-label">Résolues localement *</label>
                                    <input id="complaints_resolved_local" name="complaints_resolved_local" type="number" min="0"
                                           value="{{ old('complaints_resolved_local', 0) }}"
                                           class="form-control {{ $errors->has('complaints_resolved_local') ? 'is-invalid' : '' }}" required>
                                    @error('complaints_resolved_local')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="mb-3">
                                    <label for="complaints_transmitted" class="form-label">Transmises au niveau supérieur *</label>
                                    <input id="complaints_transmitted" name="complaints_transmitted" type="number" min="0"
                                           value="{{ old('complaints_transmitted', 0) }}"
                                           class="form-control {{ $errors->has('complaints_transmitted') ? 'is-invalid' : '' }}" required>
                                    @error('complaints_transmitted')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bloc 4 : Analyse qualitative --}}
                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <h5 class="fw-semibold mb-3 text-muted">
                            <i data-feather="message-square" style="width:16px;height:16px;"></i> Analyse qualitative
                        </h5>
                        <div class="mb-3">
                            <label for="difficulties" class="form-label">Difficultés rencontrées</label>
                            <textarea id="difficulties" name="difficulties" class="form-control" rows="4"
                                      maxlength="2000">{{ old('difficulties') }}</textarea>
                            @error('difficulties')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="solutions" class="form-label">Solutions proposées</label>
                            <textarea id="solutions" name="solutions" class="form-control" rows="4"
                                      maxlength="2000">{{ old('solutions') }}</textarea>
                            @error('solutions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="community_feedback" class="form-label">Retours de la communauté</label>
                            <textarea id="community_feedback" name="community_feedback" class="form-control" rows="4"
                                      maxlength="2000">{{ old('community_feedback') }}</textarea>
                            @error('community_feedback')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="improvement_suggestions" class="form-label">Suggestions d'amélioration</label>
                            <textarea id="improvement_suggestions" name="improvement_suggestions" class="form-control" rows="4"
                                      maxlength="2000">{{ old('improvement_suggestions') }}</textarea>
                            @error('improvement_suggestions')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="d-flex gap-2 justify-content-end">
                    <a href="{{ route('committees.show', $committee) }}" class="btn btn-secondary">Annuler</a>
                    <button type="submit" class="btn btn-success">
                        <i data-feather="save" style="width:16px;height:16px;"></i> Soumettre l'évaluation
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layout.app>
