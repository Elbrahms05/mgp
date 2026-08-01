<x-layout.app title="Profil">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Profil</li>
    </x-slot>

    {{-- Page Header with Title --}}
    <div class="row mb-4">
        <div class="col-12">
            <h5 class="text-muted mb-1">Mes paramètres de compte</h5>
            <p class="text-muted small m-0">Gérez votre profil, votre mot de passe et les paramètres de sécurité</p>
        </div>
    </div>

    {{-- Profile Cards --}}
    <div class="row">
        <div class="col-lg-8">
            
            <!-- Profil Information Card -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center">
                        <i data-feather="user" class="me-2" style="width:18px;height:18px;"></i>
                        <h6 class="mb-0">Informations de profil</h6>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <!-- Mot de passe Card -->
            <div class="card mb-4">
                <div class="card-header border-bottom">
                    <div class="d-flex align-items-center">
                        <i data-feather="lock" class="me-2" style="width:18px;height:18px;"></i>
                        <h6 class="mb-0">Sécurité</h6>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="card border-danger" hidden>
                <div class="card-header bg-light border-bottom">
                    <div class="d-flex align-items-center">
                        <i data-feather="alert-triangle" class="text-danger me-2" style="width:18px;height:18px;"></i>
                        <h6 class="mb-0 text-danger">Zone de danger</h6>
                    </div>
                </div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>

</x-layout.app>
