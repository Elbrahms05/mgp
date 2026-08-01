<section>
    <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')

        <div class="mb-3">
            <label for="update_password_current_password" class="form-label small">{{ __('Mot de passe actuel') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="update_password_password" class="form-label small">{{ __('Nouveau mot de passe') }}</label>
            <input id="update_password_password" name="password" type="password" class="form-control @error('password', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
            @error('password', 'updatePassword')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="update_password_password_confirmation" class="form-label small">{{ __('Confirmer le mot de passe') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <div class="invalid-feedback d-block small">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex align-items-center gap-3">
            <button type="submit" class="btn btn-success btn-sm">
                <i data-feather="save" class="me-1" style="width:14px;height:14px;"></i>
                {{ __('Mettre à jour le mot de passe') }}
            </button>

            @if (session('status') === 'password-updated')
                <span class="text-success small fw-bold" id="password-status">
                    <i data-feather="check-circle" class="me-1" style="width:14px;height:14px;"></i>
                    {{ __('Mot de passe mis à jour avec succès.') }}
                </span>
            @endif
        </div>
    </form>
</section>

@push('scripts')
<script>
    if (document.getElementById('password-status')) {
        setTimeout(() => {
            document.getElementById('password-status').style.display = 'none';
        }, 3000);
    }
</script>
@endpush
