<x-layout.app title="Enquête de satisfaction"
    description="Recueil de la satisfaction du plaignant à la clôture"
    icon="smile">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.show', $complaint) }}">{{ $complaint->registration_number }}</a></li>
        <li class="breadcrumb-item active">Satisfaction</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    Enquête de satisfaction - plainte {{ $complaint->registration_number }}
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('complaints.satisfaction.store', $complaint) }}">
                        @csrf

                        @php
                            $scales = [
                                'overall_rating'    => 'Satisfaction globale',
                                'timeliness_rating' => 'Respect des délais',
                                'staff_rating'      => 'Qualité de l\'accueil / du personnel',
                                'resolution_rating' => 'Résolution de la plainte',
                            ];
                        @endphp

                        @foreach ($scales as $field => $label)
                            <div class="mb-3">
                                <label class="form-label">
                                    {{ $label }}
                                    @if ($field === 'overall_rating') <span class="text-danger">*</span>@endif
                                </label>
                                <select name="{{ $field }}" class="form-select @error($field) is-invalid @enderror"
                                        @if ($field === 'overall_rating') required @endif>
                                    <option value="">- Non renseigné -</option>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <option value="{{ $i }}">{{ $i }} / 5</option>
                                    @endfor
                                </select>
                                @error($field)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach

                        <div class="mb-3">
                            <label class="form-label">Recommanderiez-vous le mécanisme ?</label>
                            <div class="form-check">
                                <input type="checkbox" name="would_recommend" value="1" class="form-check-input" id="rec">
                                <label class="form-check-label" for="rec">Oui</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Canal de recueil <span class="text-danger">*</span></label>
                            <select name="channel" class="form-select" required>
                                <option value="in_person">En personne</option>
                                <option value="phone">Téléphone</option>
                                <option value="sms">SMS</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Commentaire (facultatif)</label>
                            <textarea name="comment" rows="3" class="form-control"
                                      placeholder="Remarques du plaignant"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Enregistrer l'enquête</button>
                        <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-link">Annuler</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout.app>
