<x-layout.app title="Modifier l'utilisateur" 
description="Modifier - {{ $user->name }}"
icon="edit">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.show', $user) }}">{{ $user->name }}</a></li>
        <li class="breadcrumb-item active">Modifier</li>
    </x-slot>

    <div>
        <div class="row g-3 margin-row">
            <div class="col-12 col-md-12 offset-md-12">

                <div class="card" style="border-radius:8px;">
                    <div class="card-body">
                        
                        <form method="POST" action="{{ route('users.update', $user) }}">
                            @csrf
                            @method('PUT')

                            <div class="row g-3" style="margin-top:16px;">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="name" class="form-label">Nom complet *</label>
                                        <input type="text" id="name" name="name"
                                               value="{{ old('name', $user->name) }}"
                                               class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}" required>
                                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Adresse email *</label>
                                        <input type="email" id="email" name="email"
                                               value="{{ old('email', $user->email) }}"
                                               class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}" required>
                                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Téléphone</label>
                                        <input type="tel" id="phone" name="phone"
                                               value="{{ old('phone', $user->phone) }}" class="form-control">
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="locale" class="form-label">Langue</label>
                                        <select name="locale" id="locale" class="form-select">
                                            @foreach(['fr' => 'Français', 'ha' => 'Haoussa', 'zr' => 'Zarma/Djerma', 'tq' => 'Tamasheq', 'kr' => 'Kanouri', 'ff' => 'Fulfulde/Peul'] as $code => $label)
                                            <option value="{{ $code }}" {{ old('locale', $user->locale) === $code ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Rôle *</label>
                                        <select name="role" id="role" required class="form-select">
                                            <option value="">-- Sélectionner un rôle --</option>
                                            @foreach($roles as $role)
                                            <option value="{{ $role->name }}"
                                                    {{ old('role', $user->roles->first()?->name) === $role->name ? 'selected' : '' }}>
                                                {{ str_replace('_', ' ', ucfirst($role->name)) }}
                                            </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <hr style="margin:8px 0 16px;">
                            <p class="text-muted" style="font-size:0.82rem;">
                                <i data-feather="info" style="width:14px;height:14px;"></i>
                                Laisser vide pour ne pas changer le mot de passe.
                            </p>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="password" class="form-label">Nouveau mot de passe</label>
                                        <input type="password" id="password" name="password"
                                               class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
                                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="mb-3">
                                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="is_active" value="1"
                                               id="is_active" {{ old('is_active', $user->is_active ? '1' : '0') === '1' ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Compte actif</label>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex gap-2 justify-content-end mt-4">
                                <a href="{{ route('users.show', $user) }}" class="btn btn-link">Annuler</a>
                                <button type="submit" class="btn btn-primary">
                                    <i data-feather="save" style="width:16px;height:16px;"></i> Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

            </div>
        </div>
    </div>

</x-layout.app>
