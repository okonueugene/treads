<x-app-layout>
    <x-slot name="header">
        <x-page-header
            :title="request('size') ? request('size').' Tires' : 'Shop Tires'"
            subtitle="Filter by condition, brand, season, and price."
            class="mb-0"
        />
    </x-slot>

    <div class="page-container py-8">
        <livewire:tire-search
            :size="request('size')"
            :width="request()->integer('width') ?: null"
            :aspect_ratio="request()->integer('aspect_ratio') ?: null"
            :rim_diameter="request()->integer('rim_diameter') ?: null"
            :make="request('make')"
            :model="request('model')"
            :year="request()->integer('year') ?: null"
        />
    </div>
</x-app-layout>
