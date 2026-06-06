@props(['active'])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-brand-400 bg-brand-500/10 transition duration-150'
    : 'inline-flex items-center rounded-lg px-3 py-2 text-sm font-medium text-slate-400 transition duration-150 hover:bg-slate-800 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
