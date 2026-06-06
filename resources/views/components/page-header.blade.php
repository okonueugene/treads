@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'mb-8']) }}>
    @if (isset($breadcrumb))
        <nav class="mb-3 text-sm text-slate-400">{{ $breadcrumb }}</nav>
    @endif
    <h1 class="text-3xl font-bold text-white">{{ $title }}</h1>
    @if ($subtitle)
        <p class="mt-2 text-slate-400">{{ $subtitle }}</p>
    @endif
</div>
