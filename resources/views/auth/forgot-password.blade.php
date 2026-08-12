<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <div class="mb-4 text-sm text-gray-600">
        {{ __('Enter your email address or institutional ID below. If an account exists, your administrator will be notified to reset your password.') }}
    </div>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email or Identifier -->
        <div>
            <x-input-label for="credential" :value="__('Email or Institutional ID')" />
            <x-text-input id="credential" class="block mt-1 w-full" type="text" name="credential" :value="old('credential')" required autofocus
                placeholder="e.g. admin@uplyft.com, STU-2026/0101, EMP#402" />
            <x-input-error :messages="$errors->get('credential')" class="mt-2" />
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900" href="{{ route('login') }}">
                {{ __('Back to Login') }}
            </a>

            <x-primary-button>
                {{ __('Request Password Reset') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
