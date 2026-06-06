<x-app-layout>
    <x-slot name="header">
        <nav class="mb-2 text-sm text-slate-400">
            <a href="{{ route('shop.index') }}" class="hover:text-brand-400">Shop</a>
            <span class="mx-2">/</span>
            <span class="text-slate-300">{{ $product->title }}</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">{{ $product->title }}</h1>
    </x-slot>

    <div class="page-container py-8" x-data="{ image: '{{ product_image_url($product->image) }}' }">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="space-y-4">
                <div class="card relative overflow-hidden">
                    @if ($product->isUsed())
                        <span class="absolute left-4 top-4 z-10 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-slate-900">Used Tire</span>
                    @else
                        <span class="absolute left-4 top-4 z-10 rounded-full bg-brand-500 px-3 py-1 text-xs font-semibold text-white">New Tire</span>
                    @endif
                    @if ($product->is_verified)
                        <span class="absolute right-4 top-4 z-10 rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">Verified Listing</span>
                    @endif
                    <img :src="image" alt="{{ $product->title }}" class="aspect-square w-full object-cover">
                </div>
            </div>

            <div class="lg:sticky lg:top-24 lg:self-start space-y-6">
                <div class="card p-6 space-y-5">
                    @if ($product->brand)
                        <p class="text-sm font-medium uppercase tracking-wider text-brand-400">{{ $product->brand->name }}</p>
                    @endif

                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <p class="text-3xl font-bold text-white">{{ format_kes($product->price) }}</p>
                        @if ($product->averageRating())
                            <p class="text-sm text-amber-400">{{ str_repeat('★', (int) round($product->averageRating())) }} <span class="text-slate-400">({{ $product->averageRating() }})</span></p>
                        @endif
                    </div>

                    @if ($product->compare_price && $product->compare_price > $product->price)
                        <p class="text-sm text-slate-500 line-through">{{ format_kes($product->compare_price) }}</p>
                    @endif

                    <dl class="grid grid-cols-2 gap-3 text-sm">
                        <div class="rounded-lg bg-slate-800/50 p-3">
                            <dt class="text-slate-400">Size</dt>
                            <dd class="mt-1 font-semibold text-white">{{ $product->formattedSize() }}</dd>
                        </div>
                        <div class="rounded-lg bg-slate-800/50 p-3">
                            <dt class="text-slate-400">Availability</dt>
                            <dd class="mt-1 font-semibold {{ $product->stock > 0 ? 'text-green-400' : 'text-red-400' }}">
                                {{ $product->stock > 0 ? $product->stock.' in stock' : 'Out of stock' }}
                            </dd>
                        </div>
                        @if ($product->season)
                            <div class="rounded-lg bg-slate-800/50 p-3">
                                <dt class="text-slate-400">Season</dt>
                                <dd class="mt-1 font-semibold text-white">{{ ucfirst($product->season) }}</dd>
                            </div>
                        @endif
                        @if ($product->load_index)
                            <div class="rounded-lg bg-slate-800/50 p-3">
                                <dt class="text-slate-400">Load Index</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $product->load_index }}</dd>
                            </div>
                        @endif
                        @if ($product->speed_rating)
                            <div class="rounded-lg bg-slate-800/50 p-3">
                                <dt class="text-slate-400">Speed Rating</dt>
                                <dd class="mt-1 font-semibold text-white">{{ $product->speed_rating }}</dd>
                            </div>
                        @endif
                    </dl>

                    @if ($product->isUsed())
                        <div class="rounded-lg border border-amber-500/20 bg-amber-500/5 p-4">
                            <h3 class="font-semibold text-amber-300">Used Tire Details</h3>
                            <dl class="mt-3 grid grid-cols-2 gap-3 text-sm">
                                @if ($product->conditionGradeLabel())
                                    <div><dt class="text-slate-400">Condition</dt><dd class="text-white">{{ $product->conditionGradeLabel() }}</dd></div>
                                @endif
                                @if ($product->tread_depth_mm)
                                    <div><dt class="text-slate-400">Tread depth</dt><dd class="text-white">{{ $product->tread_depth_mm }} mm</dd></div>
                                @endif
                                @if ($product->dot_year)
                                    <div><dt class="text-slate-400">DOT</dt><dd class="text-white">{{ $product->dot_week ? 'W'.$product->dot_week.' ' : '' }}{{ $product->dot_year }}</dd></div>
                                @endif
                                @if ($product->remaining_mileage_km)
                                    <div><dt class="text-slate-400">Est. remaining</dt><dd class="text-white">{{ number_format($product->remaining_mileage_km) }} km</dd></div>
                                @endif
                            </dl>
                            @if ($product->dotAgeYears() !== null && $product->dotAgeYears() > 6)
                                <p class="mt-3 text-sm text-amber-400">This tire is over 6 years old. Please inspect carefully before purchase.</p>
                            @endif
                            @if ($product->defects)
                                <p class="mt-3 text-sm text-slate-400"><span class="text-slate-300">Defects:</span> {{ $product->defects }}</p>
                            @endif
                        </div>
                    @endif

                    <div class="rounded-lg border border-slate-700 bg-slate-800/30 p-4">
                        <h3 class="font-semibold text-white">Vendor</h3>
                        <p class="mt-1 text-white">{{ $product->vendorDisplayName() }}</p>
                        <p class="mt-1 text-sm text-slate-400">{{ $product->sold_count }} sold on marketplace</p>
                    </div>

                    @if ($product->description)
                        <div class="border-t border-slate-700 pt-4">
                            <h3 class="font-semibold text-white">Description</h3>
                            <p class="mt-2 text-sm text-slate-400">{{ $product->description }}</p>
                        </div>
                    @endif

                    @if ($product->stock > 0)
                        <div class="flex flex-col gap-3 sm:flex-row">
                            <form method="POST" action="{{ route('cart.store') }}" class="flex flex-1 gap-3">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <input type="number" name="quantity" value="1" min="1" max="{{ min(99, $product->stock) }}" class="input-field w-20">
                                <button type="submit" class="btn-primary flex-1">Add to cart</button>
                            </form>
                            <form method="POST" action="{{ route('cart.buy-now') }}">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn-accent w-full sm:w-auto">Buy now</button>
                            </form>
                        </div>
                        <form method="POST" action="{{ route('saved.toggle', $product) }}">
                            @csrf
                            <button type="submit" class="text-sm text-slate-400 transition hover:text-brand-400">
                                {{ ($isSaved ?? false) ? '♥ Saved for later' : '♡ Save for later' }}
                            </button>
                        </form>
                    @else
                        <p class="text-sm text-red-400">This product is currently out of stock.</p>
                    @endif
                </div>
            </div>
        </div>

        @if ($relatedProducts->isNotEmpty())
            <section class="mt-16">
                <h2 class="mb-6 text-xl font-bold text-white">Related Tires</h2>
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($relatedProducts as $related)
                        <x-product-card :product="$related" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
