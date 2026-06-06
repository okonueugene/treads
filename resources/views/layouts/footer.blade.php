<footer class="mt-auto border-t border-surface-border/60 bg-surface-card/40">
    <div class="page-container py-12">
        <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
            <div>
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    <x-brand-logo variant="mark" />
                </a>
                <p class="mt-3 text-sm text-surface-silver">
                    Your trusted marketplace for quality tires. Find the right fit for every vehicle.
                </p>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Shop</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-400">
                    <li><a href="{{ route('shop.index') }}" class="transition hover:text-brand-400">All Tires</a></li>
                    <li><a href="{{ route('fitment.index') }}" class="transition hover:text-brand-400">Fitment Checker</a></li>
                    <li><a href="{{ route('cart.index') }}" class="transition hover:text-brand-400">Cart</a></li>
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Account</h3>
                <ul class="mt-4 space-y-2 text-sm text-slate-400">
                    @auth
                        <li><a href="{{ route('dashboard') }}" class="transition hover:text-brand-400">Dashboard</a></li>
                        <li><a href="{{ route('profile.edit') }}" class="transition hover:text-brand-400">Profile</a></li>
                    @else
                        <li><a href="{{ route('login') }}" class="transition hover:text-brand-400">Log in</a></li>
                        <li><a href="{{ route('register') }}" class="transition hover:text-brand-400">Register</a></li>
                    @endauth
                </ul>
            </div>
            <div>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-300">Need help?</h3>
                <p class="mt-4 text-sm text-slate-400">
                    Use our fitment checker to find tires that match your vehicle year, make, and model.
                </p>
                <a href="{{ route('fitment.index') }}" class="btn-primary mt-4 inline-flex text-sm">Check Fitment</a>
            </div>
        </div>
        <div class="mt-8 border-t border-slate-800 pt-8 text-center text-sm text-slate-500">
            &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</footer>
