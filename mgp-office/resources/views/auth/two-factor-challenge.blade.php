<x-guest-layout>
    <div class="mb-4 text-sm text-gray-600">
        {{ __('Confirmez votre connexion en saisissant le code fourni par votre application d\'authentification, ou l\'un de vos codes de récupération.') }}
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('two-factor.challenge.store') }}">
        @csrf

        <div>
            <x-input-label for="code" :value="__('Code d\'authentification')" />
            <x-text-input id="code" class="block mt-1 w-full" type="text" inputmode="numeric"
                          name="code" autocomplete="one-time-code" autofocus placeholder="123456" />
        </div>

        <div class="mt-4">
            <x-input-label for="recovery_code" :value="__('Ou code de récupération')" />
            <x-text-input id="recovery_code" class="block mt-1 w-full" type="text"
                          name="recovery_code" autocomplete="one-time-code" />
            <x-input-error :messages="$errors->get('code')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('Se connecter') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
