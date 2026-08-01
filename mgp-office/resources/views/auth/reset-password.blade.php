<x-guest-layout>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email"
                   value="{{ old('email', $request->email) }}"
                   required autofocus autocomplete="username"
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">{{ __('auth.password') }}</label>
            <input id="password" type="password" name="password"
                   required autocomplete="new-password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">
                {{ __('auth.confirm_password') }}
            </label>
            <input id="password_confirmation" type="password" name="password_confirmation"
                   required autocomplete="new-password" class="form-control">
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i data-feather="key" class="me-1" style="width:15px;height:15px;"></i>
                {{ __('auth.reset_password') }}
            </button>
        </div>
    </form>

</x-guest-layout>
