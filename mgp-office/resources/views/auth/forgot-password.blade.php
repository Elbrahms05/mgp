<x-guest-layout>

    <p class="text-muted small mb-3">{{ __('auth.forgot_intro') }}</p>

    @if (session('status'))
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
        <i data-feather="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        {{ session('status') }}
    </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">{{ __('auth.email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   required autofocus
                   class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-flex justify-content-end mt-4">
            <button type="submit" class="btn btn-primary">
                <i data-feather="send" class="me-1" style="width:15px;height:15px;"></i>
                {{ __('auth.send_reset_link') }}
            </button>
        </div>
    </form>

</x-guest-layout>
