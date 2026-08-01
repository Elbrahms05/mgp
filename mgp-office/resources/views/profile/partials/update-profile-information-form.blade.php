<section>
    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')

        <div class="mb-3">
            <label for="name" class="form-label small">{{ __('Nom complet') }}</label>
            <input id="name" name="name" type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="email" class="form-label small">{{ __('Adresse e-mail') }}</label>
            <input readonly id="email" name="email" type="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required autocomplete="username" />
            @error('email')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 p-3 bg-warning-soft rounded-2">
                    <p class="text-warning small mb-2 fw-bold">
                        <i data-feather="alert-circle" class="me-1" style="width:14px;height:14px;"></i>
                        {{ __('Votre adresse e-mail n\'est pas vérifiée.') }}
                    </p>
                    <button form="send-verification" class="btn btn-sm btn-link p-0">
                        {{ __('Cliquez ici pour renvoyer l\'e-mail de vérification.') }}
                    </button>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 small text-success fw-bold mb-0">
                            {{ __('Un nouveau lien de vérification a été envoyé à votre adresse e-mail.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-success btn-sm">
                <i data-feather="save" class="me-1" style="width:14px;height:14px;"></i>
                {{ __('Enregistrer les modifications') }}
            </button>

            @if (session('status') === 'profile-updated')
                <span class="text-success small fw-bold" id="save-status">
                    <i data-feather="check-circle" class="me-1" style="width:14px;height:14px;"></i>
                    {{ __('Profil mis à jour avec succès.') }}
                </span>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    if (document.getElementById('save-status')) {
        setTimeout(() => {
            document.getElementById('save-status').style.display = 'none';
        }, 3000);
    }
</script>
@endpush
