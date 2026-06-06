<?php

use App\Models\Brand;
use App\Models\Product;
use App\Models\TireRequest;
use App\Services\FitmentService;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public bool $compact = false;

    public string $query = '';
    public string $sizeString = '';
    public ?int $width = null;
    public ?int $aspectRatio = null;
    public ?int $rimDiameter = null;
    public string $vehicleMake = '';
    public string $vehicleModel = '';
    public ?int $vehicleYear = null;

    /** @var array<int, string> */
    public array $conditions = ['new', 'used'];

    /** @var array<int, int> */
    public array $brandIds = [];

    /** @var array<int, string> */
    public array $seasons = [];

    /** @var array<int, string> */
    public array $conditionGrades = [];

    public ?float $priceMin = null;
    public ?float $priceMax = null;
    public string $sort = 'price_asc';

    public bool $showTireRequestForm = false;
    public string $requestPhone = '';
    public string $requestPreference = 'either';
    public string $requestNotes = '';
    public ?string $requestSubmitted = null;

    public function mount(
        bool $compact = false,
        ?string $size = null,
        ?int $width = null,
        ?int $aspect_ratio = null,
        ?int $rim_diameter = null,
        ?string $make = null,
        ?string $model = null,
        ?int $year = null,
    ): void {
        $this->compact = $compact;

        if ($size) {
            $this->sizeString = $size;
            try {
                $parsed = Product::parseTireSize($size);
                $this->width = $parsed['width'];
                $this->aspectRatio = $parsed['aspect_ratio'];
                $this->rimDiameter = $parsed['rim_diameter'];
            } catch (\InvalidArgumentException) {
                // Keep sizeString for display; query will return empty.
            }
        } elseif ($width && $aspect_ratio && $rim_diameter) {
            $this->width = $width;
            $this->aspectRatio = $aspect_ratio;
            $this->rimDiameter = $rim_diameter;
            $this->sizeString = sprintf('%d/%dR%d', $width, $aspect_ratio, $rim_diameter);
        }

        if ($make) {
            $this->vehicleMake = $make;
        }
        if ($model) {
            $this->vehicleModel = $model;
        }
        if ($year) {
            $this->vehicleYear = $year;
        }

    }

    public function updated($property): void
    {
        $filterProps = [
            'query', 'sizeString', 'width', 'aspectRatio', 'rimDiameter',
            'conditions', 'brandIds', 'seasons', 'conditionGrades',
            'priceMin', 'priceMax', 'sort',
        ];

        if (in_array($property, $filterProps, true)) {
            $this->resetPage();
        }
    }

    public function searchBySize(): void
    {
        if (! $this->width || ! $this->aspectRatio || ! $this->rimDiameter) {
            $this->addError('width', 'Enter a complete tire size.');

            return;
        }

        $size = sprintf('%d/%dR%d', $this->width, $this->aspectRatio, $this->rimDiameter);
        $this->redirect(route('shop.index', ['size' => $size]), navigate: true);
    }

    public function selectVehicleSize(string $size): void
    {
        $params = ['size' => $size];
        if ($this->vehicleMake) {
            $params['make'] = $this->vehicleMake;
        }
        if ($this->vehicleModel) {
            $params['model'] = $this->vehicleModel;
        }
        if ($this->vehicleYear) {
            $params['year'] = $this->vehicleYear;
        }

        $this->redirect(route('shop.index', $params), navigate: true);
    }

    public function clearFilters(): void
    {
        $this->reset([
            'query', 'sizeString', 'width', 'aspectRatio', 'rimDiameter',
            'vehicleMake', 'vehicleModel', 'vehicleYear',
            'brandIds', 'seasons', 'conditionGrades',
            'priceMin', 'priceMax', 'showTireRequestForm', 'requestSubmitted',
        ]);
        $this->conditions = ['new', 'used'];
        $this->sort = 'price_asc';
        $this->resetPage();
    }

    public function submitTireRequest(): void
    {
        if (! $this->hasActiveSizeFilter()) {
            return;
        }

        $this->validate([
            'requestPhone' => ['required', 'string', 'max:20'],
            'requestPreference' => ['required', 'in:new,used,either'],
            'requestNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        TireRequest::query()->create([
            'user_id' => auth()->id(),
            'phone' => $this->requestPhone,
            'width' => $this->width,
            'aspect_ratio' => $this->aspectRatio,
            'rim_diameter' => $this->rimDiameter,
            'make' => $this->vehicleMake ?: null,
            'model' => $this->vehicleModel ?: null,
            'year' => $this->vehicleYear,
            'preference' => $this->requestPreference,
            'status' => 'open',
            'notes' => $this->requestNotes ?: null,
        ]);

        $this->requestSubmitted = $this->activeSizeLabel();
        $this->showTireRequestForm = false;
        $this->reset(['requestNotes']);
    }

    public function hasActiveSizeFilter(): bool
    {
        return $this->width && $this->aspectRatio && $this->rimDiameter;
    }

    public function activeSizeLabel(): string
    {
        if (! $this->hasActiveSizeFilter()) {
            return '';
        }

        return sprintf('%d/%dR%d', $this->width, $this->aspectRatio, $this->rimDiameter);
    }

    protected function applySizeFilter($query): void
    {
        if ($this->sizeString !== '' && ! $this->hasActiveSizeFilter()) {
            try {
                $size = Product::parseTireSize($this->sizeString);
                $query->bySize($size);
            } catch (\InvalidArgumentException) {
                $query->whereRaw('0 = 1');
            }
        } elseif ($this->hasActiveSizeFilter()) {
            $query->bySize(Product::parseTireSize(
                width: $this->width,
                aspectRatio: $this->aspectRatio,
                rimDiameter: $this->rimDiameter,
            ));
        }
    }

    public function with(FitmentService $fitmentService): array
    {
        $vehicleSizes = collect();
        if ($this->vehicleMake && $this->vehicleModel && $this->vehicleYear && ! $this->hasActiveSizeFilter()) {
            $vehicleSizes = $fitmentService->getRecommendedSizes(
                $this->vehicleMake,
                $this->vehicleModel,
                $this->vehicleYear,
            );
        }

        if ($this->compact) {
            return ['vehicleSizes' => $vehicleSizes];
        }

        $products = Product::query()
            ->active()
            ->inStock()
            ->with(['brand', 'category', 'vendor']);

        if ($this->query !== '') {
            $ids = Product::search($this->query)->keys();
            $products->whereIn('id', $ids->isEmpty() ? [-1] : $ids);
        }

        $this->applySizeFilter($products);

        if (count($this->conditions) < count(Product::CONDITIONS)) {
            $products->whereIn('condition', $this->conditions);
        }

        if ($this->brandIds !== []) {
            $products->whereIn('brand_id', $this->brandIds);
        }

        if ($this->seasons !== []) {
            $products->whereIn('season', $this->seasons);
        }

        if ($this->conditionGrades !== []) {
            $products->whereIn('condition_grade', $this->conditionGrades);
        }

        if ($this->priceMin !== null) {
            $products->where('price', '>=', $this->priceMin);
        }

        if ($this->priceMax !== null) {
            $products->where('price', '<=', $this->priceMax);
        }

        match ($this->sort) {
            'price_desc' => $products->orderByDesc('price'),
            'newest' => $products->orderByDesc('created_at'),
            'sold' => $products->orderByDesc('sold_count'),
            'title_asc' => $products->orderBy('title'),
            default => $products->orderBy('price'),
        };

        return [
            'products' => $products->paginate(12)->withQueryString(),
            'brands' => Brand::query()->where('is_active', true)->orderBy('name')->get(),
            'vehicleSizes' => $vehicleSizes,
            'pageTitle' => $this->hasActiveSizeFilter()
                ? $this->activeSizeLabel().' Tires'
                : 'Shop Tires',
        ];
    }
};
?>

@if ($compact)
    <div class="tire-search-compact">
        <h2 class="text-lg font-semibold text-white">Search By Tire Size</h2>
        <form wire:submit="searchBySize" class="mt-4 flex flex-wrap items-end gap-3">
            <div class="flex items-center gap-2">
                <input
                    type="number"
                    wire:model="width"
                    placeholder="225"
                    class="input-field w-20 text-center"
                    min="100"
                    max="400"
                >
                <span class="text-slate-400">/</span>
                <input
                    type="number"
                    wire:model="aspectRatio"
                    placeholder="45"
                    class="input-field w-20 text-center"
                    min="25"
                    max="90"
                >
                <span class="text-slate-400">R</span>
                <input
                    type="number"
                    wire:model="rimDiameter"
                    placeholder="17"
                    class="input-field w-20 text-center"
                    min="10"
                    max="30"
                >
            </div>
            <button type="submit" class="btn-primary">Search</button>
        </form>
        @error('width') <p class="mt-2 text-sm text-red-400">{{ $message }}</p> @enderror
    </div>
@else
    <div class="tire-search" wire:key="tire-search-root" x-data="{ filtersOpen: false }">
        @if ($vehicleSizes->isNotEmpty())
            <div class="card mb-6 p-4">
                <p class="text-sm text-slate-400">
                    Select a tire size for {{ $vehicleMake }} {{ $vehicleModel }} {{ $vehicleYear }}
                </p>
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach ($vehicleSizes as $size)
                        <button
                            type="button"
                            wire:click="selectVehicleSize('{{ $size['label'] }}')"
                            class="inline-flex items-center gap-2 rounded-full border border-brand-500/40 bg-brand-500/10 px-4 py-2 text-sm font-medium text-brand-300 transition hover:border-brand-400 hover:bg-brand-500/20 hover:text-white"
                        >
                            {{ $size['label'] }}
                            @if (($size['fitment_type'] ?? 'oem') === 'oem')
                                <span class="rounded bg-brand-500/30 px-1.5 py-0.5 text-xs text-brand-200">OEM</span>
                            @else
                                <span class="rounded bg-slate-700 px-1.5 py-0.5 text-xs text-slate-300">Upgrade</span>
                            @endif
                        </button>
                    @endforeach
                </div>
            </div>
        @endif

        @if ($requestSubmitted)
            <div class="alert-success mb-6 rounded-lg border border-green-500/30 bg-green-500/10 p-4 text-sm text-green-300">
                Your request for {{ $requestSubmitted }} tires has been submitted. Vendors will be notified.
            </div>
        @endif

        <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-white">{{ $pageTitle }}</h2>
                @if ($vehicleMake && $vehicleModel && $vehicleYear)
                    <p class="text-sm text-slate-400">{{ $vehicleMake }} {{ $vehicleModel }} {{ $vehicleYear }}</p>
                @endif
            </div>
            <div class="flex items-center gap-3 lg:hidden">
                <p class="text-sm text-slate-400">{{ $products->total() }} results</p>
                <button type="button" @click="filtersOpen = true" class="btn-secondary text-sm">Filters</button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-4">
            <aside class="hidden space-y-4 lg:block">
                <div class="card sticky top-24 space-y-4 p-4">
                    @include('components.partials.tire-search-filters')
                </div>
            </aside>

            <div
                x-show="filtersOpen"
                x-transition
                class="fixed inset-0 z-50 lg:hidden"
                style="display: none;"
            >
                <div class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm" @click="filtersOpen = false"></div>
                <div class="absolute inset-y-0 left-0 w-80 overflow-y-auto bg-slate-900 p-4 shadow-card">
                    <div class="mb-4 flex items-center justify-between">
                        <h3 class="font-semibold text-white">Filters</h3>
                        <button type="button" @click="filtersOpen = false" class="text-slate-400 hover:text-white">✕</button>
                    </div>
                    @include('components.partials.tire-search-filters')
                </div>
            </div>

            <div class="lg:col-span-3">
                <div wire:loading class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                    @for ($i = 0; $i < 6; $i++)
                        <div class="card overflow-hidden">
                            <div class="skeleton h-44"></div>
                            <div class="space-y-2 p-4">
                                <div class="skeleton h-3 w-1/3"></div>
                                <div class="skeleton h-4 w-2/3"></div>
                            </div>
                        </div>
                    @endfor
                </div>

                <div wire:loading.remove>
                    @if ($products->isEmpty())
                        @if ($this->hasActiveSizeFilter())
                            <div class="card p-6">
                                <h3 class="text-lg font-semibold text-white">Can't find this tire?</h3>
                                <p class="mt-2 text-sm text-slate-400">
                                    No listings for {{ $this->activeSizeLabel() }} right now. Request it and we'll notify vendors.
                                </p>

                                @if (! $showTireRequestForm)
                                    <button type="button" wire:click="$set('showTireRequestForm', true)" class="btn-primary mt-4">
                                        Request Tire
                                    </button>
                                    <button type="button" wire:click="clearFilters" class="btn-secondary mt-4 ml-2">Clear filters</button>
                                @else
                                    <form wire:submit="submitTireRequest" class="mt-4 space-y-4">
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-300">Size</label>
                                            <input type="text" value="{{ $this->activeSizeLabel() }}" class="input-field" disabled>
                                        </div>
                                        @if ($vehicleMake)
                                            <div>
                                                <label class="mb-1 block text-sm font-medium text-slate-300">Vehicle</label>
                                                <input type="text" value="{{ $vehicleMake }} {{ $vehicleModel }} {{ $vehicleYear }}" class="input-field" disabled>
                                            </div>
                                        @endif
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-300">Preference</label>
                                            <select wire:model="requestPreference" class="input-field">
                                                <option value="either">New or Used</option>
                                                <option value="new">New only</option>
                                                <option value="used">Used only</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-300">Phone number</label>
                                            <input type="tel" wire:model="requestPhone" placeholder="0712345678" class="input-field" required>
                                            @error('requestPhone') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
                                        </div>
                                        <div>
                                            <label class="mb-1 block text-sm font-medium text-slate-300">Notes (optional)</label>
                                            <textarea wire:model="requestNotes" rows="2" class="input-field w-full"></textarea>
                                        </div>
                                        <div class="flex gap-2">
                                            <button type="submit" class="btn-primary">Submit Request</button>
                                            <button type="button" wire:click="$set('showTireRequestForm', false)" class="btn-secondary">Cancel</button>
                                        </div>
                                    </form>
                                @endif
                            </div>
                        @else
                            <x-empty-state title="No tires match your filters" description="Try adjusting your search criteria or clearing filters.">
                                <x-slot name="action">
                                    <button type="button" wire:click="clearFilters" class="btn-secondary">Clear filters</button>
                                </x-slot>
                            </x-empty-state>
                        @endif
                    @else
                        <p class="mb-4 hidden text-sm text-slate-400 lg:block">{{ $products->total() }} results</p>
                        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach ($products as $product)
                                <x-product-card :product="$product" :show-add-to-cart="false" wire:key="product-{{ $product->id }}" />
                            @endforeach
                        </div>
                        <div class="mt-6">
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endif
