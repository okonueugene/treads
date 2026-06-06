<x-account-layout title="Tire Requests" subtitle="Tires you've requested when none were in stock.">
    @if ($requests->isEmpty())
        <x-empty-state title="No tire requests" description="When you request a tire size we don't have, it will show up here.">
            <x-slot name="action">
                <a href="{{ route('shop.index') }}" class="btn-primary">Search tires</a>
            </x-slot>
        </x-empty-state>
    @else
        <div class="space-y-4">
            @foreach ($requests as $tireRequest)
                <article class="card p-5">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-lg font-semibold text-white">{{ $tireRequest->formattedSize() }}</p>
                            <p class="text-sm text-slate-400">
                                {{ ucfirst($tireRequest->preference) }} preference
                                @if ($tireRequest->make)
                                    · {{ $tireRequest->make }} {{ $tireRequest->model }} {{ $tireRequest->year }}
                                @endif
                            </p>
                            <p class="mt-1 text-xs text-slate-500">Submitted {{ $tireRequest->created_at->diffForHumans() }}</p>
                        </div>
                        <span class="rounded-full border border-slate-600 px-3 py-1 text-xs font-medium capitalize text-slate-300">
                            {{ $tireRequest->status }}
                        </span>
                    </div>
                </article>
            @endforeach
        </div>
        <div class="mt-6">{{ $requests->links() }}</div>
    @endif
</x-account-layout>
