<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Vendor Dashboard" :subtitle="auth()->user()->shop_name" class="mb-0" />
    </x-slot>

    <div class="page-container space-y-8 py-8">
        @if (session('import_stats'))
            @php $stats = session('import_stats'); @endphp
            <x-alert type="success">
                Imported {{ $stats['imported'] }}, updated {{ $stats['updated'] }}, skipped {{ $stats['skipped'] }}.
                @if (! empty($stats['errors']))
                    <ul class="mt-2 list-disc pl-5">
                        @foreach ($stats['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif
            </x-alert>
        @endif

        <div class="grid gap-6 sm:grid-cols-3">
            <x-stat-card label="Total Listings" :value="$products->count()" />
            <x-stat-card label="Low Stock" :value="$products->where('stock', '<', 5)->count()" />
            <x-stat-card label="Recent Sales" :value="$recentItems->count()" />
        </div>

        <section class="card p-6">
            <h3 class="mb-2 text-lg font-semibold text-white">Import products (CSV)</h3>
            <p class="mb-4 text-sm text-slate-400">
                Re-uploads are safe — rows match on <code class="rounded bg-slate-800 px-1">sku</code> via <code class="rounded bg-slate-800 px-1">updateOrCreate</code>.
            </p>
            <form action="{{ route('vendor.products.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-wrap items-end gap-4">
                @csrf
                <div>
                    <input type="file" name="csv" accept=".csv,text/csv" required class="block text-sm text-slate-300 file:mr-4 file:rounded-lg file:border-0 file:bg-brand-500 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-brand-600">
                    @error('csv') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                </div>
                <button type="submit" class="btn-primary">Upload CSV</button>
            </form>
            <p class="mt-4 text-xs text-slate-500">
                Required: sku, title, price, stock, width, aspect_ratio, rim_diameter.
                Optional: brand_id, category_id, description, compare_price, load_index, speed_rating, season, image, is_active.
            </p>
        </section>

        <section class="card p-6">
            <h3 class="mb-4 text-lg font-semibold text-white">Your listings ({{ $products->count() }})</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-left text-slate-400">
                            <th class="px-3 py-3">SKU</th>
                            <th class="px-3 py-3">Title</th>
                            <th class="px-3 py-3">Size</th>
                            <th class="px-3 py-3">Price</th>
                            <th class="px-3 py-3">Stock</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($products as $product)
                            <tr class="hover:bg-slate-800/30">
                                <td class="px-3 py-3 font-mono text-xs text-slate-400">{{ $product->sku }}</td>
                                <td class="px-3 py-3 text-white">{{ $product->title }}</td>
                                <td class="px-3 py-3 text-slate-300">{{ $product->formattedSize() }}</td>
                                <td class="px-3 py-3 text-white">${{ number_format($product->price, 2) }}</td>
                                <td class="px-3 py-3 {{ $product->stock < 5 ? 'text-yellow-400' : 'text-slate-300' }}">{{ $product->stock }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-slate-400">No products yet. Import a CSV to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="card p-6">
            <h3 class="mb-4 text-lg font-semibold text-white">Recent sales</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-700 text-left text-slate-400">
                            <th class="px-3 py-3">Order</th>
                            <th class="px-3 py-3">Product</th>
                            <th class="px-3 py-3">Qty</th>
                            <th class="px-3 py-3">Line total</th>
                            <th class="px-3 py-3">Commission</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse ($recentItems as $item)
                            <tr class="hover:bg-slate-800/30">
                                <td class="px-3 py-3 text-white">{{ $item->order->order_number }}</td>
                                <td class="px-3 py-3 text-slate-300">{{ $item->product_title }}</td>
                                <td class="px-3 py-3 text-slate-300">{{ $item->quantity }}</td>
                                <td class="px-3 py-3 text-white">${{ number_format($item->line_total, 2) }}</td>
                                <td class="px-3 py-3 text-slate-300">${{ number_format($item->commission_amount, 2) }} ({{ $item->commission_rate }}%)</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-slate-400">No sales yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-app-layout>
