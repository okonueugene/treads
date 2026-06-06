@props(['product', 'showAddToCart' => true])

<article {{ $attributes->merge(['class' => 'group card-hover overflow-hidden flex flex-col']) }}>
    <a href="{{ route('products.show', $product) }}" class="relative block overflow-hidden">
        <x-product-image
            :src="$product->image"
            :alt="$product->title"
            class="h-44 w-full transition-transform duration-500 group-hover:scale-105"
        />
        <div class="absolute left-3 top-3">
            @if ($product->isUsed())
                <span class="rounded-full bg-amber-500/90 px-2.5 py-1 text-xs font-semibold text-slate-900">Used Tire</span>
            @else
                <span class="rounded-full bg-brand-500/90 px-2.5 py-1 text-xs font-semibold text-white">New Tire</span>
            @endif
        </div>
        @if ($product->is_verified)
            <div class="absolute right-3 top-3">
                <span class="rounded-full bg-green-500/90 px-2 py-1 text-xs font-medium text-white">Verified</span>
            </div>
        @endif
    </a>

    <div class="flex flex-1 flex-col space-y-2 p-4">
        @if ($product->brand)
            <p class="text-xs font-medium uppercase tracking-wider text-brand-400">{{ $product->brand->name }}</p>
        @endif

        <h3 class="font-semibold text-white line-clamp-2">
            <a href="{{ route('products.show', $product) }}" class="transition hover:text-brand-400">{{ $product->title }}</a>
        </h3>

        <p class="text-sm text-slate-400">{{ $product->formattedSize() }}</p>

        @if ($product->isUsed())
            <dl class="space-y-1 text-xs text-slate-400">
                @if ($product->conditionGradeLabel())
                    <div class="flex justify-between">
                        <dt>Condition</dt>
                        <dd class="text-slate-300">{{ $product->conditionGradeLabel() }}</dd>
                    </div>
                @endif
                @if ($product->tread_depth_mm)
                    <div class="flex justify-between">
                        <dt>Tread</dt>
                        <dd class="text-slate-300">{{ $product->tread_depth_mm }} mm</dd>
                    </div>
                @endif
                @if ($product->dot_year)
                    <div class="flex justify-between">
                        <dt>DOT</dt>
                        <dd class="text-slate-300">{{ $product->dot_year }}</dd>
                    </div>
                @endif
            </dl>
        @elseif ($product->season)
            <p class="text-xs text-slate-500">{{ ucfirst($product->season) }}</p>
        @endif

        <p class="text-xs text-slate-500">Vendor: {{ $product->vendorDisplayName() }}</p>

        <div class="mt-auto flex items-center justify-between pt-2">
            <p class="text-lg font-bold text-white">{{ format_kes($product->price) }}</p>
            @if ($showAddToCart && $product->stock > 0)
                <form method="POST" action="{{ route('cart.store') }}">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="rounded-lg bg-brand-500/10 px-3 py-1.5 text-sm font-medium text-brand-400 transition hover:bg-brand-500 hover:text-white">
                        Add to cart
                    </button>
                </form>
            @else
                <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-brand-400 hover:text-brand-300">
                    View Details
                </a>
            @endif
        </div>
    </div>
</article>
