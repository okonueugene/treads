<x-account-layout title="My Orders" subtitle="Track and review your tire purchases.">
    @if ($orders->isEmpty())
        <x-empty-state title="No orders yet" description="When you purchase tires, your orders will appear here.">
            <x-slot name="action">
                <a href="{{ route('shop.index') }}" class="btn-primary">Shop tires</a>
            </x-slot>
        </x-empty-state>
    @else
        <div class="space-y-4">
            @foreach ($orders as $order)
                <article class="card p-5">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-lg font-semibold text-white">{{ $order->order_number }}</p>
                            <p class="mt-1 text-sm text-slate-400">
                                {{ $order->created_at->format('M j, Y g:i A') }}
                                · {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}
                            </p>
                        </div>
                        <div class="text-right">
                            <span class="inline-flex rounded-full border border-slate-600 px-2.5 py-0.5 text-xs font-medium capitalize text-slate-300">
                                {{ $order->status }}
                            </span>
                            <p class="mt-2 text-lg font-bold text-white">{{ format_kes($order->total) }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span class="text-xs text-slate-500">Payment: {{ ucfirst($order->payment_status ?? 'unpaid') }}</span>
                        @if ($order->delivery_method)
                            <span class="text-xs text-slate-500">· {{ $order->delivery_method === 'pickup' ? 'Pickup' : 'Home delivery' }}</span>
                        @endif
                    </div>
                    <div class="mt-4">
                        <a href="{{ route('account.orders.show', $order) }}" class="btn-secondary text-sm">View details</a>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-account-layout>
