<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Your Cart" subtitle="{{ $lines->count() }} {{ Str::plural('item', $lines->count()) }} from {{ $linesByVendor->count() }} {{ Str::plural('vendor', $linesByVendor->count()) }}" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        @if ($lines->isEmpty())
            <x-empty-state title="Your cart is empty" description="Browse our shop to find the perfect tires for your vehicle.">
                <x-slot name="action">
                    <a href="{{ route('shop.index') }}" class="btn-primary">Browse tires</a>
                </x-slot>
            </x-empty-state>
        @else
            <div class="grid gap-8 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    @foreach ($linesByVendor as $vendorId => $vendorLines)
                        @php $vendor = $vendorLines->first()['product']->vendor; @endphp
                        <section class="card overflow-hidden">
                            <div class="border-b border-slate-700 bg-slate-800/40 px-4 py-3">
                                <h2 class="font-semibold text-white">{{ $vendor?->displayName() ?? 'Vendor' }}</h2>
                                <p class="text-xs text-slate-400">{{ $vendorLines->count() }} {{ Str::plural('item', $vendorLines->count()) }}</p>
                            </div>
                            <div class="divide-y divide-slate-700">
                                @foreach ($vendorLines as $line)
                                    <div class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center">
                                        <x-product-image :src="$line['product']->image" :alt="$line['product']->title" class="h-24 w-24 shrink-0 rounded-lg" />
                                        <div class="flex-1 min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="font-semibold text-white">{{ $line['product']->title }}</h3>
                                                @if ($line['product']->isUsed())
                                                    <span class="rounded bg-amber-500/20 px-2 py-0.5 text-xs text-amber-300">Used</span>
                                                @else
                                                    <span class="rounded bg-brand-500/20 px-2 py-0.5 text-xs text-brand-300">New</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-slate-400">{{ $line['product']->formattedSize() }}</p>
                                            <p class="text-sm text-slate-500">{{ format_kes($line['product']->price) }} each</p>
                                        </div>
                                        <form method="POST" action="{{ route('cart.update', $line['product']->id) }}" class="flex items-center gap-3">
                                            @csrf
                                            @method('PATCH')
                                            <div class="flex items-center rounded-lg border border-slate-600">
                                                <button type="button" onclick="const i=this.nextElementSibling; if(+i.value>0){i.value=+i.value-1; i.form.requestSubmit()}" class="px-3 py-1 text-slate-400 hover:text-white">−</button>
                                                <input type="number" name="quantity" value="{{ $line['quantity'] }}" min="0" max="99" class="w-12 border-0 bg-transparent text-center text-sm text-white focus:ring-0">
                                                <button type="button" onclick="const i=this.previousElementSibling; if(+i.value<99){i.value=+i.value+1; i.form.requestSubmit()}" class="px-3 py-1 text-slate-400 hover:text-white">+</button>
                                            </div>
                                        </form>
                                        <form method="POST" action="{{ route('cart.destroy', $line['product']->id) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-slate-500 transition hover:text-red-400" title="Remove">✕ Remove</button>
                                        </form>
                                        <p class="text-lg font-bold text-white sm:w-28 sm:text-right">{{ format_kes($line['line_total']) }}</p>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endforeach
                </div>

                <div class="lg:sticky lg:top-24 lg:self-start">
                    <div class="card space-y-4 p-6">
                        <h3 class="text-lg font-semibold text-white">Order Summary</h3>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Subtotal</span>
                            <span class="font-semibold text-white">{{ format_kes($subtotal) }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-slate-400">Shipping</span>
                            <span class="text-slate-400">At checkout</span>
                        </div>
                        <div class="flex justify-between border-t border-slate-700 pt-4">
                            <span class="font-semibold text-white">Total</span>
                            <span class="text-xl font-bold text-white">{{ format_kes($subtotal) }}</span>
                        </div>
                        <a href="{{ route('checkout') }}" class="btn-primary w-full text-center">Proceed to checkout</a>
                        <a href="{{ route('shop.index') }}" class="block text-center text-sm text-slate-400 hover:text-brand-400">Continue shopping</a>
                    </div>
                </div>
            </div>
        @endif

        @if ($savedItems->isNotEmpty())
            <section class="mt-10">
                <h2 class="mb-4 text-lg font-semibold text-white">Saved for Later</h2>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                    @foreach ($savedItems as $saved)
                        <div class="card overflow-hidden">
                            <x-product-image :src="$saved->image" :alt="$saved->title" class="h-32 w-full" />
                            <div class="p-3 space-y-2">
                                <p class="font-medium text-white text-sm line-clamp-2">{{ $saved->title }}</p>
                                <p class="text-sm font-bold text-white">{{ format_kes($saved->price) }}</p>
                                <div class="flex gap-2">
                                    @if ($saved->stock > 0)
                                        <form method="POST" action="{{ route('cart.store') }}" class="flex-1">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $saved->id }}">
                                            <button type="submit" class="btn-primary w-full text-xs py-1.5">Move to Cart</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-slate-500 flex-1">Out of stock</span>
                                    @endif
                                    <form method="POST" action="{{ route('saved.toggle', $saved) }}">
                                        @csrf
                                        <button type="submit" class="text-xs text-slate-500 hover:text-red-400 transition" title="Remove">✕</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
