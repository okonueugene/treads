<nav x-data="{ open: false }" class="nav-glass">
    <div class="page-container">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center gap-8">
                <a href="{{ route('home') }}" class="flex shrink-0 items-center gap-3 transition hover:opacity-90">
                    <x-brand-logo variant="mark" />
                </a>

                <div class="hidden space-x-1 sm:flex">
                    <x-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.*')">
                        Shop
                    </x-nav-link>
                    <x-nav-link :href="route('home').'#vehicle-search'" :active="request()->routeIs('home')">
                        Find by Vehicle
                    </x-nav-link>
                    <x-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">
                        Cart
                    </x-nav-link>
                    @auth
                        <x-nav-link :href="route('account.index')" :active="request()->routeIs('account.*')">
                            My Account
                        </x-nav-link>
                        @can('access-vendor-dashboard')
                            <x-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">
                                Vendor
                            </x-nav-link>
                        @endcan
                    @endauth
                </div>
            </div>

            <div class="hidden items-center gap-4 sm:flex">
                <a href="{{ route('cart.index') }}" class="relative rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-brand-400" aria-label="Cart">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-brand-500 px-1 text-xs font-bold text-white">
                            {{ $cartCount > 99 ? '99+' : $cartCount }}
                        </span>
                    @endif
                </a>

                @auth
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800/50 px-3 py-2 text-sm font-medium text-slate-200 transition hover:border-slate-600 hover:bg-slate-800">
                                <span class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-500/20 text-xs font-bold text-brand-400">
                                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                                </span>
                                <span class="hidden md:inline">{{ Auth::user()->name }}</span>
                                <svg class="h-4 w-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </x-slot>
                        <x-slot name="content">
                            <x-dropdown-link :href="route('account.index')">My Account</x-dropdown-link>
                            <x-dropdown-link :href="route('account.orders.index')">My Orders</x-dropdown-link>
                            <x-dropdown-link :href="route('profile.edit')">{{ __('Profile') }}</x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                @else
                    <a href="{{ route('login') }}" class="text-sm text-slate-400 transition hover:text-white">Log in</a>
                    <a href="{{ route('register') }}" class="btn-primary text-sm">Register</a>
                @endauth
            </div>

            <div class="flex items-center gap-2 sm:hidden">
                <a href="{{ route('cart.index') }}" class="relative rounded-lg p-2 text-slate-400">
                    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    @if (($cartCount ?? 0) > 0)
                        <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-brand-500 text-[10px] font-bold text-white">{{ $cartCount }}</span>
                    @endif
                </a>
                <button @click="open = !open" class="rounded-lg p-2 text-slate-400 transition hover:bg-slate-800 hover:text-white">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': !open}" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        <path :class="{'hidden': !open, 'inline-flex': open}" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="border-t border-slate-800 sm:hidden"
        style="display: none;"
    >
        <div class="space-y-1 px-4 py-3">
            <x-responsive-nav-link :href="route('shop.index')" :active="request()->routeIs('shop.*')">Shop</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('home').'#vehicle-search'" :active="request()->routeIs('home')">Find by Vehicle</x-responsive-nav-link>
            <x-responsive-nav-link :href="route('cart.index')" :active="request()->routeIs('cart.*')">Cart</x-responsive-nav-link>
            @auth
                <x-responsive-nav-link :href="route('account.index')" :active="request()->routeIs('account.*')">My Account</x-responsive-nav-link>
                @can('access-vendor-dashboard')
                    <x-responsive-nav-link :href="route('vendor.dashboard')" :active="request()->routeIs('vendor.*')">Vendor</x-responsive-nav-link>
                @endcan
            @endauth
        </div>
        @auth
            <div class="border-t border-slate-800 px-4 py-3">
                <div class="text-sm font-medium text-white">{{ Auth::user()->name }}</div>
                <div class="text-xs text-slate-400">{{ Auth::user()->email }}</div>
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile.edit')">{{ __('Profile') }}</x-responsive-nav-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-responsive-nav-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">{{ __('Log Out') }}</x-responsive-nav-link>
                    </form>
                </div>
            </div>
        @else
            <div class="border-t border-slate-800 px-4 py-3 space-y-2">
                <a href="{{ route('login') }}" class="block text-sm text-slate-400 hover:text-white">Log in</a>
                <a href="{{ route('register') }}" class="btn-primary inline-flex text-sm">Register</a>
            </div>
        @endauth
    </div>
</nav>
