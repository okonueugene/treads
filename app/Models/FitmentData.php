<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class FitmentData extends Model
{
    protected $fillable = [
        'make',
        'model',
        'year_from',
        'year_to',
        'width',
        'aspect_ratio',
        'rim_diameter',
        'fitment_type',
        'position',
        'trim',
    ];

    /**
     * Expand a year range (e.g. 2015–2024) into individual years.
     *
     * @return list<int>
     */
    public static function yearsFor(int $yearFrom, int $yearTo): array
    {
        if ($yearFrom > $yearTo) {
            [$yearFrom, $yearTo] = [$yearTo, $yearFrom];
        }

        return range($yearFrom, $yearTo);
    }

    public function formattedSize(): string
    {
        return "{$this->width}/{$this->aspect_ratio}R{$this->rim_diameter}";
    }

    public function scopeForVehicle(Builder $query, string $make, string $model, int $year): Builder
    {
        return $query
            ->where('make', $make)
            ->where('model', $model)
            ->where('year_from', '<=', $year)
            ->where('year_to', '>=', $year);
    }

    public function scopeMatchingSizes(Builder $query, array $sizes): Builder
    {
        return $query->where(function (Builder $q) use ($sizes) {
            foreach ($sizes as $size) {
                $q->orWhere(function (Builder $inner) use ($size) {
                    $inner->where('width', $size['width'])
                        ->where('aspect_ratio', $size['aspect_ratio'])
                        ->where('rim_diameter', $size['rim_diameter']);
                });
            }
        });
    }
}
