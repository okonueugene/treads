<x-account-layout title="My Account" subtitle="Manage your orders, addresses, and requests.">
    <div class="grid gap-4 sm:grid-cols-3">
        <x-stat-card label="Recent orders" :value="$recentOrders->count()" />
        <x-stat-card label="Open tire requests" :value="$openRequests" />
        <x-stat-card label="Saved items" :value="$savedCount" />
    </div>

    <div class="card mt-6 p-6">
        <div class="mb-4 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Recent Orders</h2>
            <a href="{{ route('account.orders.index') }}" class="text-sm text-brand-400 hover:text-brand-300">View all</a>
        </div>

        @if ($recentOrders->isEmpty())
            <p class="text-sm text-slate-400">You haven't placed any orders yet.</p>
            <a href="{{ route('shop.index') }}" class="btn-primary mt-4 inline-flex">Browse tires</a>
        @else
            <div class="divide-y divide-slate-700">
                @foreach ($recentOrders as $order)
                    <div class="flex flex-wrap items-center justify-between gap-3 py-4">
                        <div>
                            <p class="font-medium text-white">{{ $order->order_number }}</p>
                            <p class="text-sm text-slate-400">{{ $order->created_at->format('M j, Y') }} · {{ ucfirst($order->status) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <p class="font-semibold text-white">{{ format_kes($order->total) }}</p>
                            <a href="{{ route('account.orders.show', $order) }}" class="btn-secondary text-sm">View</a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-account-layout>
