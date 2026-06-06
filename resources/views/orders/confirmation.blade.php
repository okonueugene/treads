<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Order Confirmed" :subtitle="$order->order_number" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        <div class="mx-auto max-w-2xl">
            <div class="card p-8 text-center">
                <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-500/20 text-green-400">
                    <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="mt-4 text-2xl font-bold text-white">Order Successfully Created</h1>
                <p class="mt-2 text-slate-400">Thank you for your purchase. We'll notify you when your order is processed.</p>

                <dl class="mt-8 space-y-3 rounded-lg border border-slate-700 bg-slate-800/30 p-5 text-left text-sm">
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Order number</dt>
                        <dd class="font-semibold text-white">{{ $order->order_number }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Payment status</dt>
                        <dd class="font-semibold capitalize text-white">{{ $order->payment_status ?? 'unpaid' }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Order status</dt>
                        <dd class="font-semibold capitalize text-white">{{ $order->status }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Total</dt>
                        <dd class="font-semibold text-white">{{ format_kes($order->total) }}</dd>
                    </div>
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-400">Delivery</dt>
                        <dd class="text-white">{{ $order->delivery_method === 'pickup' ? 'Pickup' : 'Home delivery' }}</dd>
                    </div>
                </dl>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ route('orders.show', $order) }}" class="btn-primary">Track order</a>
                    @auth
                        <a href="{{ route('account.orders.index') }}" class="btn-secondary">My orders</a>
                    @endauth
                    <a href="{{ route('shop.index') }}" class="btn-secondary">Continue shopping</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
