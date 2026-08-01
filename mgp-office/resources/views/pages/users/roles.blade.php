<x-layout.app title="Rôles & Permissions" 
description="13 rôles définis selon le manuel MGP PACIPA"
icon="gate">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
        <li class="breadcrumb-item active">Rôles & Permissions</li>
    </x-slot>


    @foreach($roles as $role)
    <div class="card margin-row" style="border-radius:8px;margin-bottom:12px;">
        <div class="card-body" style="padding:16px 24px;">
            <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                <span style="font-weight:600;font-size:1rem;">
                    <i data-feather="shield" style="width:16px;height:16px;vertical-align:middle;"></i>
                    {{ str_replace('_', ' ', ucfirst($role->name)) }}
                </span>
                <span class="badge" style="background:#6C757D;border-radius:4px;font-size:0.75rem;padding:2px 8px;">
                    {{ $role->permissions->count() }} permission(s)
                </span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:4px;">
                @foreach($role->permissions->sortBy('name') as $perm)
                <span class="badge" style="background:#e8f0fe;color:#E05206;border-radius:4px;font-size:0.7rem;padding:2px 8px;">
                    {{ $perm->name }}
                </span>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach

</x-layout.app>
