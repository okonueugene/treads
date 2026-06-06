<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="'Order '.$order->order_number" :subtitle="'Placed '.$order->created_at->format('M j, Y')" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        <div class="mx-auto max-w-3xl space-y-6">
            @if (session('status'))
                <div class="rounded-lg border border-green-500/30 bg-green-500/10 p-3 text-sm text-green-300">{{ session('status') }}</div>
            @endif

            <div class="card p-6">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        @php
                            $statusColors = [
                                'pending' => 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30',
                                'paid' => 'bg-green-500/10 text-green-400 border-green-500/30',
                                'processing' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                'shipped' => 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30',
                                'delivered' => 'bg-green-500/10 text-green-400 border-green-500/30',
                                'cancelled' => 'bg-red-500/10 text-red-400 border-red-500/30',
                            ];
                            $statusClass = $statusColors[$order->status] ?? 'bg-slate-500/10 text-slate-400 border-slate-500/30';
                        @endphp
                        <span class="inline-flex rounded-full border px-3 py-1 text-sm font-medium capitalize {{ $statusClass }}">{{ $order->status }}</span>
                        <p class="mt-3 text-3xl font-bold text-white">{{ format_kes($order->total) }}</p>
                        <p class="mt-1 text-sm text-slate-400">Payment: <span class="capitalize text-slate-300">{{ $order->payment_status ?? 'unpaid' }}</span></p>
                    </div>
                    @if ($fromAccount ?? false)
                        <a href="{{ route('account.orders.index') }}" class="btn-secondary text-sm">← All orders</a>
                    @endif
                </div>
                <p class="mt-4 text-sm text-slate-400">
                    {{ $order->delivery_method === 'pickup' ? 'Pickup' : 'Deliver to' }}
                    {{ $order->shipping_name }},
                    {{ $order->shipping_address }},
                    {{ $order->shipping_town }},
                    {{ $order->shipping_county }}
                    @if ($order->shipping_landmark) ({{ $order->shipping_landmark }}) @endif
                </p>
            </div>

            <div class="card p-6">
                <h3 class="mb-4 font-semibold text-white">Order Progress</h3>
                <ol class="relative space-y-0">
                    @foreach ($order->timelineSteps() as $index => $step)
                        <li class="flex gap-4 pb-6 last:pb-0">
                            <div class="flex flex-col items-center">
                                <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-bold {{ $step['complete'] ? 'bg-brand-500 text-white' : 'bg-slate-700 text-slate-400' }}">
                                    @if ($step['complete']) ✓ @else {{ $index + 1 }} @endif
                                </span>
                                @if (! $loop->last)
                                    <div class="mt-1 h-full w-px min-h-[2rem] {{ $step['complete'] ? 'bg-brand-500/50' : 'bg-slate-700' }}"></div>
                                @endif
                            </div>
                            <div class="pt-1">
                                <p class="font-medium {{ $step['current'] ? 'text-brand-400' : 'text-white' }}">{{ $step['label'] }}</p>
                                @if ($step['current'])
                                    <p class="text-sm text-slate-400">Current status</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            <div class="card p-6">
                <h3 class="mb-4 font-semibold text-white">Items by vendor</h3>
                @foreach ($order->items->groupBy('vendor_id') as $vendorItems)
                    @php $vendor = $vendorItems->first()->vendor; @endphp
                    <div class="mb-6 last:mb-0">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-700 pb-2">
                            <p class="font-medium text-white">{{ $vendor?->displayName() ?? 'Vendor' }}</p>
                            <span class="text-xs capitalize text-slate-400">{{ $vendorItems->first()->vendor_status }}</span>
                        </div>
                        <div class="space-y-3">
                            @foreach ($vendorItems as $item)
                                <div class="flex justify-between gap-3 text-sm">
                                    <div>
                                        <p class="font-medium text-white">{{ $item->product_title }}</p>
                                        <p class="text-slate-400">SKU {{ $item->product_sku }} · Qty {{ $item->quantity }}</p>
                                        @if ($item->tracking_number)
                                            <p class="mt-1 text-xs text-brand-400">Tracking: {{ $item->tracking_number }}</p>
                                        @endif
                                    </div>
                                    <p class="font-semibold text-white">{{ format_kes($item->line_total) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            @if (in_array($order->status, ['shipped', 'delivered', 'processing', 'paid'], true) && ! $order->receipt_confirmed_at)
                <div class="card p-6">
                    <h3 class="font-semibold text-white">Confirm delivery</h3>
                    <p class="mt-1 text-sm text-slate-400">Received your tires? Confirm receipt to complete this order.</p>
                    <form method="POST" action="{{ route('orders.confirm-receipt', $order) }}" class="mt-4">
                        @csrf
                        <x-primary-button>Confirm receipt</x-primary-button>
                    </form>
                </div>
            @endif

            @auth
            @if ($order->receipt_confirmed_at || $order->status === 'delivered')
                <div class="card space-y-6 p-6">
                    <h3 class="font-semibold text-white">Leave a review</h3>
                    @foreach ($order->items->unique('product_id') as $item)
                        <form method="POST" action="{{ route('orders.reviews.store', $order) }}" class="space-y-3 border-b border-slate-700 pb-5 last:border-0">
                            @csrf
                            <input type="hidden" name="type" value="product">
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <p class="text-sm font-medium text-white">Rate: {{ $item->product_title }}</p>
                            <select name="rating" class="input-field w-32" required>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} stars</option>
                                @endfor
                            </select>
                            <textarea name="body" rows="2" class="input-field w-full" placeholder="Your review (optional)"></textarea>
                            <button type="submit" class="btn-secondary text-sm">Submit product review</button>
                        </form>
                    @endforeach
                    @foreach ($order->items->unique('vendor_id') as $item)
                        <form method="POST" action="{{ route('orders.reviews.store', $order) }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="type" value="vendor">
                            <input type="hidden" name="vendor_id" value="{{ $item->vendor_id }}">
                            <p class="text-sm font-medium text-white">Rate vendor: {{ $item->vendor?->displayName() }}</p>
                            <select name="rating" class="input-field w-32" required>
                                @for ($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}">{{ $i }} stars</option>
                                @endfor
                            </select>
                            <textarea name="body" rows="2" class="input-field w-full" placeholder="Vendor feedback (optional)"></textarea>
                            <button type="submit" class="btn-secondary text-sm">Submit vendor review</button>
                        </form>
                    @endforeach
                </div>
            @endif
            @endauth

            @foreach ($order->items->unique('product_id') as $item)
                @if ($item->product_id)
                    <div class="text-center">
                        <form method="POST" action="{{ route('cart.buy-now') }}">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                            <button type="submit" class="btn-accent">Buy {{ $item->product_title }} again</button>
                        </form>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
</x-app-layout>
