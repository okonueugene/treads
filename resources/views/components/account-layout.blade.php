@props(['title', 'subtitle' => null])

<x-app-layout>
    <x-slot name="header">
        <x-page-header :title="$title" :subtitle="$subtitle" class="mb-0" />
    </x-slot>

    <div class="page-container py-8">
        <div class="grid gap-8 lg:grid-cols-4">
            <aside class="lg:col-span-1">
                @include('account.partials.nav')
            </aside>
            <div class="lg:col-span-3">
                {{ $slot }}
            </div>
        </div>
    </div>
</x-app-layout>
