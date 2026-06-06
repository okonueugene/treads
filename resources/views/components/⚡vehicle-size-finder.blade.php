<?php

use App\Services\FitmentService;
use Livewire\Component;

new class extends Component
{
    public string $make = '';
    public string $model = '';
    public ?int $year = null;

    /** @var \Illuminate\Support\Collection<int, array<string, mixed>> */
    public $recommendedSizes;

    public bool $searched = false;

    public function mount(): void
    {
        $this->recommendedSizes = collect();
    }

    public function updatedMake(): void
    {
        $this->model = '';
        $this->year = null;
        $this->searched = false;
        $this->recommendedSizes = collect();
    }

    public function updatedModel(): void
    {
        $this->year = null;
        $this->searched = false;
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
        $this->searched = true;
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

<div id="vehicle-search" class="vehicle-size-finder">
    <h2 class="text-lg font-semibold text-white">Find Tires For Your Vehicle</h2>

    <form wire:submit="search" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
            <div wire:loading wire:target="make" class="skeleton h-10 rounded-lg"></div>
            <select wire:model.live="model" class="input-field" @disabled($make === '') wire:loading.remove wire:target="make">
                <option value="">Select model</option>
                @foreach ($models as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @error('model') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="mb-1 block text-sm font-medium text-slate-300">Year</label>
            <div wire:loading wire:target="model" class="skeleton h-10 rounded-lg"></div>
            <select wire:model="year" class="input-field" @disabled($model === '') wire:loading.remove wire:target="model">
                <option value="">Select year</option>
                @foreach ($years as $option)
                    <option value="{{ $option }}">{{ $option }}</option>
                @endforeach
            </select>
            @error('year') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-end">
            <button type="submit" class="btn-primary w-full" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="search">Find Tires</span>
                <span wire:loading wire:target="search">Finding...</span>
            </button>
        </div>
    </form>

    <div wire:loading wire:target="search" class="mt-6 flex flex-wrap gap-2">
        @for ($i = 0; $i < 3; $i++)
            <div class="skeleton h-10 w-28 rounded-full"></div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="search">
        @if ($searched && $recommendedSizes->isNotEmpty())
            <div class="mt-6">
                <p class="text-sm text-slate-400">Recommended sizes for {{ $make }} {{ $model }} {{ $year }}</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($recommendedSizes as $size)
                        <a
                            href="{{ route('shop.index', [
                                'size' => $size['label'],
                                'make' => $make,
                                'model' => $model,
                                'year' => $year,
                            ]) }}"
                            class="inline-flex items-center gap-2 rounded-full border border-brand-500/40 bg-brand-500/10 px-4 py-2 text-sm font-medium text-brand-300 transition hover:border-brand-400 hover:bg-brand-500/20 hover:text-white"
                        >
                            {{ $size['label'] }}
                            @if (($size['fitment_type'] ?? 'oem') === 'oem')
                                <span class="rounded bg-brand-500/30 px-1.5 py-0.5 text-xs text-brand-200">OEM</span>
                            @else
                                <span class="rounded bg-slate-700 px-1.5 py-0.5 text-xs text-slate-300">Upgrade</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>
        @elseif ($searched)
            <p class="mt-6 text-sm text-slate-400">No fitment data found for this vehicle. Try searching by tire size below.</p>
        @endif
    </div>
</div>
