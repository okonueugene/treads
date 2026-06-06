<?php

use App\Services\FitmentService;
use Livewire\Component;

new class extends Component
{
    public string $make = '';
    public string $model = '';
    public ?int $year = null;

    /** @var \Illuminate\Support\Collection<int, \App\Models\Product> */
    public $products;

    /** @var \Illuminate\Support\Collection<int, array<string, mixed>> */
    public $recommendedSizes;

    public bool $showSizes = false;

    public function mount(): void
    {
        $this->products = collect();
        $this->recommendedSizes = collect();
    }

    public function updatedMake(): void
    {
        $this->model = '';
        $this->year = null;
        $this->showSizes = false;
        $this->products = collect();
        $this->recommendedSizes = collect();
    }

    public function updatedModel(): void
    {
        $this->year = null;
        $this->showSizes = false;
        $this->products = collect();
        $this->recommendedSizes = collect();
    }

    public function search(FitmentService $fitmentService): void
    {
        $this->validate([
            'make' => ['required', 'string', 'max:100'],
            'model' => ['required', 'string', 'max:100'],
            'year' => ['required', 'integer', 'min:1980', 'max:2030'],
        ]);

        $this->recommendedSizes = $fitmentService->getRecommendedSizes($this->make, $this->model, $this->year);
        $this->products = $fitmentService->getProductsForSizes($this->recommendedSizes);
        $this->showSizes = true;
    }

    public function with(FitmentService $fitmentService): array
    {
        return [
            'makes' => $fitmentService->getMakes(),
            'models' => $fitmentService->getModels($this->make),
            'years' => $fitmentService->getYears($this->make, $this->model),
        ];
    }
};
?>

<div class="fitment-checker">
    <div class="card p-6">
        <form wire:submit="search" class="grid gap-4 md:grid-cols-4">
            <div>
                <label class="mb-1 block text-sm font-medium text-slate-300">Make</label>
                <select wire:model.live="make" class="input-field">
                    <option value="">Select make</option>
                    @foreach ($makes as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('make') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-300">Model</label>
                <select wire:model.live="model" class="input-field" @disabled($make === '')>
                    <option value="">Select model</option>
                    @foreach ($models as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('model') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-slate-300">Year</label>
                <select wire:model="year" class="input-field" @disabled($model === '')>
                    <option value="">Select year</option>
                    @foreach ($years as $option)
                        <option value="{{ $option }}">{{ $option }}</option>
                    @endforeach
                </select>
                @error('year') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-end">
                <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">Find tires</button>
            </div>
        </form>
    </div>

    <div wire:loading.remove wire:target="search" class="mt-8">
        @if ($showSizes && $recommendedSizes->isNotEmpty())
            <div class="mb-6 flex flex-wrap gap-2">
                @foreach ($recommendedSizes as $size)
                    <a
                        href="{{ route('shop.index', ['size' => $size['label'], 'make' => $make, 'model' => $model, 'year' => $year]) }}"
                        class="inline-flex items-center gap-2 rounded-full border border-brand-500/40 bg-brand-500/10 px-4 py-2 text-sm font-medium text-brand-300"
                    >
                        {{ $size['label'] }}
                    </a>
                @endforeach
            </div>
        @endif

        @if ($products->isNotEmpty())
            <h3 class="mb-4 text-lg font-semibold text-white">
                Compatible tires for {{ $make }} {{ $model }} {{ $year }}
                <span class="text-slate-400">({{ $products->count() }} found)</span>
            </h3>
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" wire:key="fitment-product-{{ $product->id }}" />
                @endforeach
            </div>
        @elseif ($showSizes)
            <x-empty-state
                title="No tires found"
                :description="'No compatible tires for ' . $make . ' ' . $model . ' ' . $year . '. Try requesting this tire on the shop page.'"
            >
                <x-slot name="action">
                    <a href="{{ route('shop.index') }}" class="btn-primary">Browse all tires</a>
                </x-slot>
            </x-empty-state>
        @endif
    </div>
</div>
