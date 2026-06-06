<x-account-layout title="My Reviews" subtitle="Feedback you've left for products and vendors.">
    @if ($reviews->isEmpty())
        <x-empty-state title="No reviews yet" description="After delivery, you can review products and vendors from your order page.">
            <x-slot name="action">
                <a href="{{ route('account.orders.index') }}" class="btn-primary">View orders</a>
            </x-slot>
        </x-empty-state>
    @else
        <div class="space-y-4">
            @foreach ($reviews as $review)
                <article class="card p-5">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-semibold text-white">
                            {{ $review->type === 'product' ? ($review->product?->title ?? 'Product') : ($review->vendor?->displayName() ?? 'Vendor') }}
                        </p>
                        <p class="text-amber-400">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</p>
                    </div>
                    @if ($review->body)
                        <p class="mt-2 text-sm text-slate-400">{{ $review->body }}</p>
                    @endif
                    <p class="mt-2 text-xs text-slate-500">{{ $review->created_at->format('M j, Y') }} · {{ ucfirst($review->type) }} review</p>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $reviews->links() }}</div>
    @endif
</x-account-layout>
