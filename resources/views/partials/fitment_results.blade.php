@php
$products = $products ?? [];
@endphp

@if (count($products) === 0)
    <p class="text-center text-slate-400">No tires found for {{ $make }} {{ $model }} {{ $year }}.</p>
@else
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ($products as $p)
            <article class="card-hover overflow-hidden rounded-xl border border-slate-700 bg-surface-card">
                <img src="{{ $p['img'] }}" alt="{{ $p['title'] }}" class="h-40 w-full object-cover">
                <div class="space-y-2 p-4">
                    <h3 class="font-semibold text-white">{{ $p['title'] }}</h3>
                    <p class="text-sm text-slate-400">{{ $p['size'] }}</p>
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-white">{{ $p['price'] }}</p>
                        <form method="POST" action="{{ url('/cart') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $p['id'] ?? '' }}">
                            <button type="submit" class="rounded-lg bg-brand-500/10 px-3 py-1.5 text-sm font-medium text-brand-400 hover:bg-brand-500 hover:text-white">Add to cart</button>
                        </form>
                    </div>
                </div>
            </article>
        @endforeach
    </div>
@endif
