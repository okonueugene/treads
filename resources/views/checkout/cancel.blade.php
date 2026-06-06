<x-app-layout>
    <div class="page-container flex min-h-[60vh] items-center justify-center py-16">
        <div class="card max-w-md p-8 text-center animate-slide-up">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-yellow-500/10 text-yellow-400">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Payment Cancelled</h1>
            <p class="mt-3 text-slate-400">Your payment was not completed. Your cart items are still saved.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('cart.index') }}" class="btn-primary">Return to Cart</a>
                <a href="{{ route('shop.index') }}" class="btn-secondary">Continue Shopping</a>
            </div>
        </div>
    </div>
</x-app-layout>
