@php
use App\Models\Product;
$featuredProducts = Product::query()->active()->inStock()->with('brand')->limit(8)->get();
@endphp

<x-marketing-layout>
    <x-slot name="hero">
        <section class="relative overflow-hidden border-b border-surface-border/60">
            <div class="absolute inset-0 bg-gradient-to-br from-black via-surface-card to-brand-900/40"></div>
            <div class="absolute inset-0 opacity-30" style="background-image: radial-gradient(circle at 70% 30%, rgba(0,174,239,0.3) 0%, transparent 50%);"></div>
            <div class="page-container relative py-12 lg:py-16">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-block rounded-full border border-brand-500/30 bg-brand-500/10 px-4 py-1 text-sm font-medium text-brand-400">The Digital Tread</span>
                    <h1 class="mt-4 text-3xl font-bold leading-tight text-white sm:text-4xl lg:text-5xl">
                        Find the perfect tires for your vehicle
                    </h1>
                    <p class="mt-4 text-slate-300">
                        Search by vehicle or tire size — get to matching products in two clicks.
                    </p>
                </div>

                <div class="mx-auto mt-10 max-w-4xl">
                    <div class="card space-y-6 p-6 lg:p-8">
                        <livewire:vehicle-size-finder />

                        <div class="flex items-center gap-4">
                            <div class="h-px flex-1 bg-slate-700"></div>
                            <span class="text-sm font-medium uppercase tracking-wider text-slate-500">Or</span>
                            <div class="h-px flex-1 bg-slate-700"></div>
                        </div>

                        <livewire:tire-search :compact="true" />
                    </div>
                </div>
            </div>
        </section>
    </x-slot>

    @if ($featuredProducts->isNotEmpty())
        <section class="border-t border-surface-border/60 bg-surface-card/30 py-16">
            <div class="page-container">
                <div class="mb-8 flex items-end justify-between">
                    <x-page-header title="Customer Favorites" subtitle="Most popular picks from our marketplace." class="mb-0" />
                    <a href="{{ route('shop.index') }}" class="btn-secondary hidden sm:inline-flex">View all</a>
                </div>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featuredProducts as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif
</x-marketing-layout>
