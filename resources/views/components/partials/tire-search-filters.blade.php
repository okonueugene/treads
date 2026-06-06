<div>
    <label class="mb-1 block text-sm font-medium text-slate-300">Search</label>
    <input
        type="search"
        wire:model.live.debounce.300ms="query"
        placeholder="Brand, model, SKU..."
        class="input-field"
    >
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-300">Tire size</label>
    <input
        type="text"
        wire:model.live.debounce.300ms="sizeString"
        placeholder="225/45R17"
        class="input-field"
    >
</div>

<div class="grid grid-cols-3 gap-2">
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-300">Width</label>
        <input type="number" wire:model.live="width" placeholder="225" class="input-field text-sm">
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-300">Aspect</label>
        <input type="number" wire:model.live="aspectRatio" placeholder="45" class="input-field text-sm">
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-300">Rim</label>
        <input type="number" wire:model.live="rimDiameter" placeholder="17" class="input-field text-sm">
    </div>
</div>

<fieldset>
    <legend class="mb-2 text-sm font-medium text-slate-300">Condition</legend>
    <div class="space-y-2">
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" value="new" wire:model.live="conditions" class="rounded border-slate-600 bg-slate-800 text-brand-500">
            New
        </label>
        <label class="flex items-center gap-2 text-sm text-slate-300">
            <input type="checkbox" value="used" wire:model.live="conditions" class="rounded border-slate-600 bg-slate-800 text-brand-500">
            Used
        </label>
    </div>
</fieldset>

@if (in_array('used', $conditions, true))
    <fieldset>
        <legend class="mb-2 text-sm font-medium text-slate-300">Used condition</legend>
        <div class="space-y-2">
            @foreach (['excellent' => 'Excellent', 'very_good' => 'Very Good', 'good' => 'Good', 'fair' => 'Fair'] as $value => $label)
                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" value="{{ $value }}" wire:model.live="conditionGrades" class="rounded border-slate-600 bg-slate-800 text-brand-500">
                    {{ $label }}
                </label>
            @endforeach
        </div>
    </fieldset>
@endif

<fieldset>
    <legend class="mb-2 text-sm font-medium text-slate-300">Brand</legend>
    <div class="max-h-40 space-y-2 overflow-y-auto">
        @foreach ($brands as $brand)
            <label class="flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" value="{{ $brand->id }}" wire:model.live="brandIds" class="rounded border-slate-600 bg-slate-800 text-brand-500">
                {{ $brand->name }}
            </label>
        @endforeach
    </div>
</fieldset>

<fieldset>
    <legend class="mb-2 text-sm font-medium text-slate-300">Season</legend>
    <div class="space-y-2">
        @foreach (['all-season' => 'All Season', 'summer' => 'Summer', 'winter' => 'Winter', 'performance' => 'Performance'] as $value => $label)
            <label class="flex items-center gap-2 text-sm text-slate-300">
                <input type="checkbox" value="{{ $value }}" wire:model.live="seasons" class="rounded border-slate-600 bg-slate-800 text-brand-500">
                {{ $label }}
            </label>
        @endforeach
    </div>
</fieldset>

<div class="grid grid-cols-2 gap-2">
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-300">Min price (KES)</label>
        <input type="number" wire:model.live.debounce.300ms="priceMin" placeholder="0" class="input-field text-sm" min="0">
    </div>
    <div>
        <label class="mb-1 block text-xs font-medium text-slate-300">Max price (KES)</label>
        <input type="number" wire:model.live.debounce.300ms="priceMax" placeholder="100000" class="input-field text-sm" min="0">
    </div>
</div>

<div>
    <label class="mb-1 block text-sm font-medium text-slate-300">Sort</label>
    <select wire:model.live="sort" class="input-field">
        <option value="price_asc">Price: low to high</option>
        <option value="price_desc">Price: high to low</option>
        <option value="newest">Newest</option>
        <option value="sold">Most popular</option>
        <option value="title_asc">Name A–Z</option>
    </select>
</div>

<button type="button" wire:click="clearFilters" class="btn-secondary w-full">
    Clear filters
</button>
