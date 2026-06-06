@props([
    'variant' => 'mark', // mark | full | wordmark
])

@php
$appName = config('app.name', 'The Digital Tread');
@endphp

@if ($variant === 'wordmark')
    <div {{ $attributes->merge(['class' => 'flex flex-col leading-none']) }}>
        <span class="text-[10px] font-semibold uppercase tracking-[0.2em] text-brand-500">The</span>
        <span class="text-lg font-bold uppercase tracking-wide text-white">Digital</span>
        <span class="text-xs font-semibold uppercase tracking-[0.15em] text-brand-500">Tread</span>
    </div>
@elseif ($variant === 'full')
    <img
        {{ $attributes->merge(['class' => 'h-auto max-h-24 w-auto max-w-full object-contain']) }}
        src="{{ asset('images/logo_design_400.png') }}"
        alt="{{ $appName }}"
    />
@else
    {{-- Crops to center logo from the 3-up brand sheet --}}
    <div {{ $attributes->merge(['class' => 'relative h-10 w-36 overflow-hidden sm:h-11 sm:w-40']) }}>
        <img
            src="{{ asset('images/logo_mark_128.png') }}"
            alt="{{ $appName }}"
            class="absolute left-1/2 top-0 h-11 max-w-none -translate-x-1/2 sm:h-12"
            style="width: 128px;"
        />
    </div>
@endif
