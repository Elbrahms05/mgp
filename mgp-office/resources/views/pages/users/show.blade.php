<x-layout.app title="Profil utilisateur">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
        <li class="breadcrumb-item active">{{ $user->name }}</li>
    </x-slot>

    <div class="row g-3 margin-row">
        {{-- Informations --}}
        <div class="col-12 col-md-4">
            <div class="card" style="border-radius:8px;">
                <div class="card-body text-center" style="padding-top:32px;">
                    <div style="width:72px;height:72px;border-radius:50%;background:#E05206;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                        <i data-feather="user" style="width:36px;height:36px;color:white;"></i>
                    </div>
                    <h6 style="margin:0;">{{ $user->name }}</h6>
                    <p class="text-muted" style="margin:4px 0;">{{ $user->email }}</p>
                    @if($user->phone)
                    <p class="text-muted" style="font-size:0.85rem;margin:0;">
                        <i data-feather="phone" style="width:14px;height:14px;"></i> {{ $user->phone }}
                    </p>
                    @endif

                    <div style="margin:12px 0;">
                        @foreach($user->roles as $role)
                        <span class="badge" style="background:#E05206;border-radius:4px;font-size:0.72rem;padding:4px 10px;">
                            {{ str_replace('_', ' ', $role->name) }}
                        </span>
                        @endforeach
                    </div>

                    <p style="font-size:0.8rem;color:#9e9e9e;">
                        Langue : {{ strtoupper($user->locale ?? 'fr') }}
                    </p>

                    @if($user->is_active)
                    <span class="text-success" style="font-size:0.85rem;">
                        <i data-feather="check-circle" style="width:14px;height:14px;"></i> Compte actif
                    </span>
                    @else
                    <span class="text-danger" style="font-size:0.85rem;">
                        <i data-feather="x" style="width:14px;height:14px;"></i> Compte inactif
                    </span>
                    @endif
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i data-feather="edit-2" style="width:16px;height:16px;"></i> Modifier
                    </a>
                </div>
            </div>

            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3" style="font-size:1rem;">Informations connexion</h5>
                    <p class="text-muted" style="font-size:0.85rem;margin:4px 0;">
                        <strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y') }}
                    </p>
                    <p class="text-muted" style="font-size:0.85rem;margin:4px 0;">
                        <strong>Dernière connexion :</strong><br>
                        {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}
                    </p>
                    <p class="text-muted" style="font-size:0.85rem;margin:4px 0;">
                        <strong>2FA :</strong>
                        {{ $user->two_factor_enabled ? 'Activé' : 'Désactivé' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Journal activité --}}
        <div class="col-12 col-md-8">
            <div class="card" style="border-radius:8px;">
                <div class="card-body">
                    <h5 class="fw-semibold mb-3">Journal d'activité récent</h5>
                    @if($activities->isEmpty())
                    <p class="text-muted">Aucune activité enregistrée.</p>
                    @else
                    <div class="table-responsive">
                    <table class="table table-hover align-middle" style="font-size:0.85rem;">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Action</th>
                                <th>Objet</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($activities as $activity)
                            <tr>
                                <td class="text-muted">{{ $activity->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    @php
                                    $badgeBg = match($activity->event) {
                                        'created' => '#009A44',
                                        'updated' => '#E05206',
                                        'deleted' => '#DC3545',
                                        default   => '#6C757D',
                                    };
                                    @endphp
                                    <span class="badge" style="background:{{ $badgeBg }};border-radius:4px;font-size:0.7rem;padding:2px 6px;">
                                        {{ $activity->event ?? 'action' }}
                                    </span>
                                </td>
                                <td class="text-muted">
                                    {{ class_basename($activity->subject_type ?? '') }}
                                    @if($activity->subject_id) #{{ $activity->subject_id }} @endif
                                    @if($activity->description) - {{ Str::limit($activity->description, 60) }} @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</x-layout.app>
