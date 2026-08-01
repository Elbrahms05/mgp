<x-guest-layout>

    <div class="text-center mb-3">
        <i data-feather="shield" style="width:36px;height:36px;color:#e65100;"></i>
    </div>

    <p class="text-muted small mb-3">{{ __('auth.confirm_password_msg') }}</p>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('auth.password') }}</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i data-feather="check-circle" class="me-1" style="width:15px;height:15px;"></i>
                {{ __('auth.confirm') }}
            </button>
        </div>
    </form>

</x-guest-layout>
