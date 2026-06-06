<x-app-layout>
    <x-slot name="header">
        <x-page-header
            title="Fitment Checker"
            subtitle="Enter your vehicle details to find compatible tires."
            class="mb-0"
        />
    </x-slot>

    <div class="page-container py-8">
        <livewire:fitment-checker />
    </div>
</x-app-layout>
