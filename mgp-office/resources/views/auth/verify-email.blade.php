<x-guest-layout>

    <div class="text-center mb-3">
        <i data-feather="mail" style="width:36px;height:36px;color:#2e7d32;"></i>
    </div>

    <p class="text-muted small mb-3">{{ __('auth.verify_email_msg') }}</p>

    @if (session('status') == 'verification-link-sent')
    <div class="alert alert-success d-flex align-items-center gap-2 mb-3" role="alert">
        <i data-feather="check-circle" style="width:15px;height:15px;flex-shrink:0;"></i>
        {{ __('auth.verification_sent') }}
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mt-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">
                <i data-feather="send" class="me-1" style="width:15px;height:15px;"></i>
                {{ __('auth.resend_email') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-link text-secondary small">
                {{ __('auth.logout') }}
            </button>
        </form>
    </div>

</x-guest-layout>
