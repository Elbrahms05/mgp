<x-layout.app title="Formation des personnes de confiance"
    description="Registre de formation et recyclage (6 mois) — module VBG"
    icon="shield">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('vbg.index') }}">VBG</a></li>
        <li class="breadcrumb-item active">Formations personnes de confiance</li>
    </x-slot>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        <div class="col-lg-7">
            <div class="card mb-4">
                <div class="card-header">État des personnes de confiance</div>
                <div class="card-body p-0">
                    <table class="table mb-0">
                        <thead>
                            <tr><th>Personne</th><th>Recyclage valable jusqu'au</th><th>Statut</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($trustedPersons as $tp)
                                <tr>
                                    <td>{{ $tp['user']->name }}</td>
                                    <td>{{ $tp['valid_until']?->format('d/m/Y') ?? '—' }}</td>
                                    <td>
                                        @if ($tp['up_to_date'])
                                            <span class="badge bg-success">À jour</span>
                                        @else
                                            <span class="badge bg-danger">Recyclage requis</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-muted">Aucune personne de confiance enregistrée.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card mb-4">
                <div class="card-header">Enregistrer une formation / recyclage</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('vbg.trusted-person.trainings.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Personne de confiance</label>
                            <select name="user_id" class="form-select @error('user_id') is-invalid @enderror" required>
                                <option value="">— Choisir —</option>
                                @foreach ($trustedPersons as $tp)
                                    <option value="{{ $tp['user']->id }}">{{ $tp['user']->name }}</option>
                                @endforeach
                            </select>
                            @error('user_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Date de la formation</label>
                            <input type="date" name="trained_at" max="{{ now()->format('Y-m-d') }}"
                                   class="form-control @error('trained_at') is-invalid @enderror" required>
                            @error('trained_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Thème (facultatif)</label>
                            <input type="text" name="topic" class="form-control"
                                   placeholder="Approche centrée survivante, consentement…">
                        </div>
                        <button type="submit" class="btn btn-primary">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout.app>
