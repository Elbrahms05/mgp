<x-layout.app 
    title="Utilisateurs" 
    description="Gestion des utilisateurs et des rôles"
    icon="user-check">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Utilisateurs</li>
    </x-slot>

    <x-slot name="actions">
        <a href="{{ route('users.create') }}" class="btn btn-success btn-sm">
            <i data-feather="user-plus" style="width:16px;height:16px;"></i> Nouvel utilisateur
        </a>
    </x-slot>

    <div x-data="{}" class="margin-row">

        {{-- Filtres --}}
        <div class="card" style="border-radius:8px;">
            <div class="card-body" style="padding:16px 24px 8px;">
                <form method="GET" action="{{ route('users.index') }}">
                    <div class="row g-3 mb-0">
                        <div class="col-12 col-md-4">
                            <div class="mb-3">
                                <label for="search" class="form-label">Recherche</label>
                                <input type="text" id="search" name="search"
                                       value="{{ request('search') }}"
                                       placeholder="Nom, email..." class="form-control">
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="filter_role" class="form-label">Rôle</label>
                                <select name="role" id="filter_role" class="form-select">
                                    <option value="">Tous les rôles</option>
                                    @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') === $role->name ? 'selected' : '' }}>
                                        {{ str_replace('_', ' ', ucfirst($role->name)) }}
                                    </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="mb-3">
                                <label for="filter_active" class="form-label">Statut</label>
                                <select name="is_active" id="filter_active" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actifs</option>
                                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactifs</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-2 d-flex align-items-end pb-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="search" style="width:16px;height:16px;"></i>
                                </button>
                                <a href="{{ route('users.index') }}" class="btn btn-link">
                                    <i data-feather="x" style="width:16px;height:16px;"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i data-feather="check-circle" class="me-2" style="width:16px;height:16px;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i data-feather="alert-circle" class="me-2" style="width:16px;height:16px;"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <div class="card" style="border-radius:8px;">
            <div class="card-body p-0">
                @if($users->isEmpty())
                <div style="text-align:center;padding:48px;color:#9e9e9e;">
                    <i data-feather="users" style="width:48px;height:48px;"></i>
                    <p>Aucun utilisateur trouvé.</p>
                </div>
                @else
                <div class="table-responsive">
                <table class="table table-hover align-middle" style="margin:0;">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Rôle</th>
                            <th>Langue</th>
                            <th>Dernière connexion</th>
                            <th class="text-center">Statut</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <a href="{{ route('users.show', $user) }}" style="color:#E05206;">
                                    <strong>{{ $user->name }}</strong>
                                </a>
                            </td>
                            <td class="text-muted" style="font-size:0.85rem;">{{ $user->email }}</td>
                            <td>
                                @foreach($user->roles as $role)
                                <span class="badge" style="background:#E05206;border-radius:4px;font-size:0.72rem;padding:2px 6px;">
                                    {{ str_replace('_', ' ', $role->name) }}
                                </span>
                                @endforeach
                            </td>
                            <td class="text-muted" style="font-size:0.8rem;">{{ strtoupper($user->locale ?? 'fr') }}</td>
                            <td class="text-muted" style="font-size:0.8rem;">
                                {{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Jamais' }}
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                <span class="text-success" style="font-size:0.8rem;">
                                    <i data-feather="check-circle" style="width:14px;height:14px;"></i> Actif
                                </span>
                                @else
                                <span class="text-danger" style="font-size:0.8rem;">
                                    <i data-feather="x" style="width:14px;height:14px;"></i> Inactif
                                </span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-link btn-sm" title="Voir">
                                    <i data-feather="eye" style="width:16px;height:16px;"></i>
                                </a>
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-link btn-sm text-warning" title="Modifier">
                                    <i data-feather="edit-2" style="width:16px;height:16px;"></i>
                                </a>
                                @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('users.destroy', $user) }}" style="display:inline;"
                                      onsubmit="return confirm('Supprimer « {{ $user->name }} » ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm text-danger" title="Supprimer">
                                        <i data-feather="trash-2" style="width:16px;height:16px;"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
                @endif
            </div>
            @if($users->hasPages())
            <div class="card-footer text-center">
                {{ $users->links() }}
            </div>
            @endif
        </div>

    </div>

    @push('scripts')
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof feather !== 'undefined') feather.replace();
    });
    </script>
    @endpush

</x-layout.app>
