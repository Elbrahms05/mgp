<x-guest-layout>

    @if (session('status'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
        <i data-feather="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Email --}}
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus autocomplete="username"
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Mot de passe --}}
        <div class="mb-3">
            <label for="password" class="form-label">{{ __('auth.password') }}</label>
            <input id="password" type="password" name="password"
                   required autocomplete="current-password"
                   class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- Se souvenir de moi --}}
        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
            <label class="form-check-label" for="remember_me">
                {{ __('auth.remember_me') }}
            </label>
        </div>

        <div class="d-flex justify-content-between align-items-center mt-4">
            @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}" class="small text-primary">
                {{ __('auth.forgot_password') }}
            </a>
            @endif
            <button type="submit" class="btn btn-primary">
                <i data-feather="log-in" class="me-1" style="width:15px;height:15px;"></i>
                {{ __('auth.login') }}
            </button>
        </div>
    </form>

</x-guest-layout>
