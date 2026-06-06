@props(['active'])

@php
$classes = ($active ?? false)
    ? 'block w-full rounded-lg bg-brand-500/10 px-3 py-2 text-start text-base font-medium text-brand-400 transition duration-150'
    : 'block w-full rounded-lg px-3 py-2 text-start text-base font-medium text-slate-400 transition duration-150 hover:bg-slate-800 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
