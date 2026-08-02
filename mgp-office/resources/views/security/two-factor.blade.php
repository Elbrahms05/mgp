<x-layout.app title="Authentification à deux facteurs"
    description="Sécurisez votre compte avec une application d'authentification"
    icon="shield">

    <x-slot name="breadcrumb">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Accueil</a></li>
        <li class="breadcrumb-item active">Authentification à deux facteurs</li>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-xl-8">

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="card mb-4">
                <div class="card-header"><i class="fas fa-shield-alt me-2"></i>Authentification à deux facteurs (2FA)</div>
                <div class="card-body">

                    @if (! $enabled)
                        <p>Renforcez la sécurité de votre compte : après votre mot de passe, un code à
                           usage unique généré par une application (Google Authenticator, FreeOTP…) sera exigé.</p>
                        <p class="text-muted">L'activation du 2FA est <strong>obligatoire</strong> pour les rôles
                           super administrateur, coordonnateur UGP et spécialiste VBG.</p>
                        <form method="POST" action="{{ route('two-factor.enable') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i> Activer l'authentification à deux facteurs
                            </button>
                        </form>

                    @elseif (! $confirmed)
                        <p>1. Scannez ce QR code avec votre application d'authentification :</p>
                        <div class="my-3">{!! $qrSvg !!}</div>
                        @if ($secretKey)
                            <p class="text-muted">Ou saisissez la clé manuellement :
                                <code>{{ $secretKey }}</code></p>
                        @endif

                        <p class="mt-4">2. Saisissez le code à 6 chiffres affiché pour confirmer :</p>
                        <form method="POST" action="{{ route('two-factor.confirm') }}" class="row g-2">
                            @csrf
                            <div class="col-auto">
                                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code"
                                       class="form-control @error('code') is-invalid @enderror"
                                       placeholder="123456" required autofocus>
                                @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div class="col-auto">
                                <button type="submit" class="btn btn-success">Confirmer</button>
                            </div>
                        </form>

                    @else
                        <div class="alert alert-success mb-4">
                            <i class="fas fa-check-circle me-1"></i>
                            L'authentification à deux facteurs est <strong>active</strong> sur votre compte.
                        </div>

                        <h6>Codes de récupération</h6>
                        <p class="text-muted">Conservez ces codes en lieu sûr : ils permettent de vous connecter
                           si vous perdez l'accès à votre application d'authentification.</p>
                        <ul class="list-inline">
                            @foreach ($recoveryCodes as $rc)
                                <li class="list-inline-item"><code>{{ $rc }}</code></li>
                            @endforeach
                        </ul>

                        <div class="d-flex gap-2 mt-3">
                            <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    Régénérer les codes de récupération
                                </button>
                            </form>
                            <form method="POST" action="{{ route('two-factor.disable') }}"
                                  onsubmit="return confirm('Désactiver le 2FA ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm">Désactiver</button>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</x-layout.app>
