<x-guest-layout>
    <h2 class="mb-2 text-2xl font-bold text-white">{{ __('Forgot Password') }}</h2>
    <p class="mb-6 text-sm text-slate-400">{{ __('No problem. Enter your email and we will send you a reset link.') }}</p>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>
        <div class="mt-6 flex justify-end">
            <x-primary-button>{{ __('Email Password Reset Link') }}</x-primary-button>
        </div>
    </form>
</x-guest-layout>
