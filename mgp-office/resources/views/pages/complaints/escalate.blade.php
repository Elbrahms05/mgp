<x-layout.app :title="'Escalader - ' . $complaint->registration_number">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.index') }}">Plaintes</a></li>
        <li class="breadcrumb-item"><a href="{{ route('complaints.show', $complaint) }}">{{ $complaint->registration_number }}</a></li>
        <li class="breadcrumb-item active">Escalader</li>
    </x-slot>

    <div class="row">
        <div class="col-12 col-md-8 offset-md-2 col-lg-6 offset-lg-3">

            <div class="card text-white" style="border-radius:8px;margin-bottom:0;background-color:#c62828;">
                <div class="card-body" style="padding:16px 24px;">
                    <div style="font-size:0.8rem;opacity:0.8;">Escalade - Plainte</div>
                    <h5 style="margin:4px 0 0;">{{ $complaint->registration_number }}</h5>
                    <div style="opacity:0.85;font-size:0.85rem;">
                        {{ Str::limit($complaint->subject, 80) }}
                        <br>
                        Niveau actuel : <strong>{{ $complaint->current_level?->value ?? 'CBGP' }}</strong>
                    </div>
                </div>
            </div>

            @if($errors->any())
            <div class="alert alert-danger mt-3" style="border-radius:8px;">
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form method="POST" action="{{ route('complaints.escalate.store', $complaint) }}" class="mt-3">
                @csrf

                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="new_level" class="form-label">Escalader vers *</label>
                            <select name="new_level" id="new_level"
                                    class="form-select {{ $errors->has('new_level') ? 'is-invalid' : '' }}" required>
                                <option value="" disabled selected>-- Choisir le niveau --</option>
                                @foreach($availableLevels as $level)
                                <option value="{{ $level->value }}" {{ old('new_level') === $level->value ? 'selected' : '' }}>
                                    {{ $level->label() }}
                                </option>
                                @endforeach
                            </select>
                            @error('new_level')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label for="comment" class="form-label">Motif de l'escalade * (min. 10 caractères)</label>
                            <textarea id="comment" name="comment"
                                      class="form-control {{ $errors->has('comment') ? 'is-invalid' : '' }}"
                                      minlength="10" rows="5"
                                      required>{{ old('comment') }}</textarea>
                            @error('comment')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <a href="{{ route('complaints.show', $complaint) }}" class="btn btn-secondary me-2">
                        Annuler
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i data-feather="arrow-up" class="me-1" style="width:15px;height:15px;"></i>Confirmer l'escalade
                    </button>
                </div>

            </form>
        </div>
    </div>

</x-layout.app>
