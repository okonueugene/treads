<x-app-layout>
    <x-slot name="hero">
        {{ $hero ?? '' }}
    </x-slot>

    {{ $slot }}
</x-app-layout>
