<x-app-layout>
    <div class="page-container flex min-h-[60vh] items-center justify-center py-16">
        <div class="card max-w-md p-8 text-center animate-slide-up">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-green-500/10 text-green-400">
                <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Payment Successful</h1>
            <p class="mt-3 text-slate-400">Thank you for your order. You will receive a confirmation shortly.</p>
            <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                <a href="{{ route('shop.index') }}" class="btn-primary">Continue Shopping</a>
                <a href="{{ route('dashboard') }}" class="btn-secondary">View Dashboard</a>
            </div>
        </div>
    </div>
</x-app-layout>
