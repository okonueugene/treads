<x-app-layout>
    <x-slot name="header">
        <nav class="mb-2 text-sm text-slate-400">
            <a href="{{ route('shop.index') }}" class="hover:text-brand-400">Shop</a>
            <span class="mx-2">/</span>
            <span class="text-slate-300">{{ $product->title }}</span>
        </nav>
        <h1 class="text-2xl font-bold text-white">{{ $product->title }}</h1>
    </x-slot>

    @php $galleryImages = $product->allImages(); @endphp
    <div class="page-container py-8" x-data="{
        images: {{ Js::from($galleryImages) }},
        active: 0,
        get current() { return this.images[this.active] ?? this.images[0]; }
    }">
        <div class="grid gap-8 lg:grid-cols-2">
            <div class="space-y-3">
                {{-- Main image --}}
                <div class="card relative overflow-hidden">
                    @if ($product->isUsed())
                        <span class="absolute left-4 top-4 z-10 rounded-full bg-amber-500 px-3 py-1 text-xs font-semibold text-slate-900">Used Tire</span>
                    @else
                        <span class="absolute left-4 top-4 z-10 rounded-full bg-brand-500 px-3 py-1 text-xs font-semibold text-white">New Tire</span>
                    @endif
                    @if ($product->is_verified)
                        <span class="absolute right-4 top-4 z-10 rounded-full bg-green-500 px-3 py-1 text-xs font-semibold text-white">Verified Listing</span>
                    @endif
                    <img :src="current" :key="current" alt="{{ $product->title }}" class="aspect-square w-full object-cover transition-opacity duration-200">
                </div>

                {{-- Thumbnail strip — only shown when there are multiple images --}}
                @if (count($galleryImages) > 1)
                    <div class="flex gap-2 overflow-x-auto pb-1">
                        <template x-for="(img, i) in images" :key="i">
                            <button
                                type="button"
                                @click="active = i"
                                :class="active === i ? 'ring-2 ring-brand-500 ring-offset-2 ring-offset-slate-900' : 'opacity-60 hover:opacity-100'"
                                class="h-16 w-16 shrink-0 overflow-hidden rounded-lg transition"
                            >
                                <img :src="img" alt="" class="h-full w-full object-cover">
                            </button>
                        </template>
                    </div>
                @endif
            </div>

            <div class="lg:sticky lg:top-24 lg:self-start space-y-6">
                <div class="card p-6 space-y-5">
                    @if ($product->brand)
                        <p class="text-sm font-medium uppercase tracking-wider text-brand-400">{{ $product->brand->name }}</p>
                    @endif

                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <p class="text-3xl font-bold text-white">{{ format_kes($product->price) }}</p>
                        @php $rating = $product->averageRating(); $reviewCount = $product->reviews()->where('type', 'product')->count(); @endphp
                        @if ($rating !== null)
                            <div class="flex items-center gap-1.5">
                                @php $full = (int) round($rating); @endphp
                                <span class="text-amber-400">{{ str_repeat('★', $full) }}{{ str_repeat('☆', 5 - $full) }}</span>
                                <span class="text-sm text-slate-400">{{ $rating }} ({{ $reviewCount }} {{ Str::plural('review', $reviewCount) }})</span>
                            </div>
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
                        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Sold by</h3>
                        <p class="mt-1 text-base font-semibold text-white">{{ $product->vendorDisplayName() }}</p>
                        @if ($product->vendor)
                            @php
                                $vendorRating = $product->vendor->averageVendorRating();
                                $vendorReviews = $product->vendor->vendorReviewCount();
                            @endphp
                            <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
                                @if ($vendorRating !== null)
                                    <span class="text-amber-400">{{ str_repeat('★', (int) round($vendorRating)) }}{{ str_repeat('☆', 5 - (int) round($vendorRating)) }}</span>
                                    <span class="text-slate-400">{{ $vendorRating }} ({{ $vendorReviews }} {{ Str::plural('review', $vendorReviews) }})</span>
                                @else
                                    <span class="text-slate-500 text-xs">No vendor reviews yet</span>
                                @endif
                                <span class="text-slate-600">·</span>
                                <span class="text-slate-400">{{ $product->sold_count }} sold</span>
                            </div>
                        @else
                            <p class="mt-1 text-sm text-slate-400">{{ $product->sold_count }} sold on marketplace</p>
                        @endif
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

        @php $productReviews = $product->reviews()->where('type', 'product')->with('user')->latest()->take(6)->get(); @endphp
        @if ($productReviews->isNotEmpty())
            <section class="mt-16">
                <h2 class="mb-6 text-xl font-bold text-white">Customer Reviews</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($productReviews as $review)
                        <div class="card p-5">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-medium text-white">{{ $review->user?->name ?? 'Verified Buyer' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $review->created_at->format('M j, Y') }}</p>
                                </div>
                                <span class="shrink-0 text-amber-400">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                            </div>
                            @if ($review->body)
                                <p class="mt-3 text-sm text-slate-400">{{ $review->body }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

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
