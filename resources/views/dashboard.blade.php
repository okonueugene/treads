<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }}" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <a href="{{ route('shop.index') }}" class="card-hover group p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-white">Shop</h3>
                <p class="mt-1 text-sm text-slate-400">Browse all tires</p>
            </a>
            <a href="{{ route('fitment.index') }}" class="card-hover group p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-white">Fitment</h3>
                <p class="mt-1 text-sm text-slate-400">Find tires for your vehicle</p>
            </a>
            <a href="{{ route('cart.index') }}" class="card-hover group p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-white">Cart</h3>
                <p class="mt-1 text-sm text-slate-400">View your cart</p>
            </a>
            <a href="{{ route('profile.edit') }}" class="card-hover group p-6">
                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400 transition group-hover:bg-brand-500 group-hover:text-white">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <h3 class="mt-4 font-semibold text-white">Profile</h3>
                <p class="mt-1 text-sm text-slate-400">Manage your account</p>
            </a>
        </div>
    </div>
</x-app-layout>
