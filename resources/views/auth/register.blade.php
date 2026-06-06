<x-guest-layout>
    <h2 class="mb-6 text-2xl font-bold text-white">{{ __('Create an account') }}</h2>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="mt-1 block w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="mt-1 block w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="mt-1 block w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="mt-1 block w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="mt-4">
            <label class="inline-flex items-center">
                <input id="is_vendor" type="checkbox" name="is_vendor" class="rounded border-slate-600 bg-slate-800 text-brand-500 focus:ring-brand-500">
                <span class="ml-2 text-sm text-slate-400">Register as vendor (create a shop)</span>
            </label>
        </div>

        <div id="vendor-fields" class="mt-4" style="display:none">
            <x-input-label for="shop_name" :value="__('Shop name')" />
            <x-text-input id="shop_name" class="mt-1 block w-full" type="text" name="shop_name" :value="old('shop_name')" />
            <x-input-error :messages="$errors->get('shop_name')" class="mt-2" />
        </div>

        <div class="mt-6 flex items-center justify-end">
            <x-primary-button>{{ __('Register') }}</x-primary-button>
        </div>

        <p class="mt-6 text-center text-sm text-slate-400">
            {{ __('Already registered?') }}
            <a href="{{ route('login') }}" class="text-brand-400 hover:text-brand-300">{{ __('Log in') }}</a>
        </p>

        <script>
            document.getElementById('is_vendor').addEventListener('change', function () {
                document.getElementById('vendor-fields').style.display = this.checked ? 'block' : 'none';
            });
        </script>
    </form>
</x-guest-layout>
