@props(['label', 'value', 'icon' => null])

<div {{ $attributes->merge(['class' => 'card p-6 animate-slide-up']) }}>
    <div class="flex items-start justify-between">
        <div>
            <p class="text-sm font-medium text-slate-400">{{ $label }}</p>
            <p class="mt-2 text-3xl font-bold text-white">{{ $value }}</p>
        </div>
        @if ($icon)
            <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-brand-500/10 text-brand-400">
                {{ $icon }}
            </div>
        @endif
    </div>
    @if (isset($footer))
        <div class="mt-4 border-t border-slate-700 pt-4 text-sm text-slate-400">{{ $footer }}</div>
    @endif
</div>
