<?php

namespace App\Services;

use App\Models\FitmentData;
use App\Models\Product;
use Illuminate\Support\Collection;

class FitmentService
{
    /**
     * @return Collection<int, string>
     */
    public function getMakes(): Collection
    {
        return FitmentData::query()->distinct()->orderBy('make')->pluck('make');
    }

    /**
     * @return Collection<int, string>
     */
    public function getModels(string $make): Collection
    {
        if ($make === '') {
            return collect();
        }

        return FitmentData::query()
            ->where('make', $make)
            ->distinct()
            ->orderBy('model')
            ->pluck('model');
    }

    /**
     * @return Collection<int, int>
     */
    public function getYears(string $make, string $model): Collection
    {
        if ($make === '' || $model === '') {
            return collect();
        }

        return FitmentData::query()
            ->where('make', $make)
            ->where('model', $model)
            ->get(['year_from', 'year_to'])
            ->flatMap(fn ($row) => FitmentData::yearsFor($row->year_from, $row->year_to))
            ->unique()
            ->sortDesc()
            ->values();
    }

    /**
     * @return Collection<int, array{width: int, aspect_ratio: int, rim_diameter: int, fitment_type: string, label: string}>
     */
    public function getRecommendedSizes(string $make, string $model, int $year): Collection
    {
        $fitments = FitmentData::query()
            ->forVehicle($make, $model, $year)
            ->get();

        if ($fitments->isEmpty()) {
            return collect();
        }

        return $fitments
            ->map(fn (FitmentData $f) => [
                'width' => $f->width,
                'aspect_ratio' => $f->aspect_ratio,
                'rim_diameter' => $f->rim_diameter,
                'fitment_type' => $f->fitment_type ?? 'oem',
                'label' => $f->formattedSize(),
            ])
            ->unique(fn (array $s) => $s['label'])
            ->sortBy(fn (array $s) => $s['fitment_type'] === 'oem' ? 0 : 1)
            ->values();
    }

    /**
     * @param  Collection<int, array{width: int, aspect_ratio: int, rim_diameter: int}>  $sizes
     * @return Collection<int, Product>
     */
    public function getProductsForSizes(Collection $sizes, int $limit = 24): Collection
    {
        if ($sizes->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->active()
            ->inStock()
            ->where(function ($query) use ($sizes) {
                foreach ($sizes as $size) {
                    $query->orWhere(function ($q) use ($size) {
                        $q->where('width', $size['width'])
                            ->where('aspect_ratio', $size['aspect_ratio'])
                            ->where('rim_diameter', $size['rim_diameter']);
                    });
                }
            })
            ->with(['brand', 'vendor'])
            ->limit($limit)
            ->get();
    }
}
