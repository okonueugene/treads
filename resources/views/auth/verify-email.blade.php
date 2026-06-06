<x-guest-layout>
    <h2 class="mb-2 text-2xl font-bold text-white">{{ __('Verify Email') }}</h2>
    <p class="mb-6 text-sm text-slate-400">{{ __('Thanks for signing up! Please verify your email by clicking the link we sent you. If you did not receive it, we can send another.') }}</p>

    @if (session('status') == 'verification-link-sent')
        <x-alert type="success" class="mb-4">{{ __('A new verification link has been sent to the email address you provided during registration.') }}</x-alert>
    @endif

    <div class="flex items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>{{ __('Resend Verification Email') }}</x-primary-button>
        </form>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="text-sm text-slate-400 hover:text-white">{{ __('Log Out') }}</button>
        </form>
    </div>
</x-guest-layout>
