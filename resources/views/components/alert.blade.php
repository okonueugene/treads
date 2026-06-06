@props(['type' => 'info'])

@php
$classes = match ($type) {
    'success' => 'border-green-500/30 bg-green-500/10 text-green-300',
    'error' => 'border-red-500/30 bg-red-500/10 text-red-300',
    'warning' => 'border-yellow-500/30 bg-yellow-500/10 text-yellow-300',
    default => 'border-brand-500/30 bg-brand-500/10 text-brand-200',
};
@endphp

<div {{ $attributes->merge(['class' => "rounded-lg border px-4 py-3 text-sm $classes"]) }} role="alert">
    {{ $slot }}
</div>
