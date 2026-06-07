<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;
use Laravel\Scout\Searchable;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory, Searchable;

    public const CONDITIONS = ['new', 'used'];

    public const CONDITION_GRADES = ['excellent', 'very_good', 'good', 'fair'];

    protected $fillable = [
        'vendor_id',
        'brand_id',
        'category_id',
        'sku',
        'title',
        'description',
        'price',
        'compare_price',
        'stock',
        'condition',
        'condition_grade',
        'tread_depth_mm',
        'dot_week',
        'dot_year',
        'remaining_mileage_km',
        'defects',
        'is_verified',
        'sold_count',
        'width',
        'aspect_ratio',
        'rim_diameter',
        'load_index',
        'speed_rating',
        'season',
        'image',
        'images',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'compare_price' => 'decimal:2',
            'tread_depth_mm' => 'decimal:1',
            'is_active' => 'boolean',
            'is_verified' => 'boolean',
            'sold_count' => 'integer',
            'images' => 'array',
        ];
    }

    /**
     * Returns all gallery image URLs: primary first, then additional images.
     * Always returns at least one entry (the primary image placeholder).
     *
     * @return array<int, string>
     */
    public function allImages(): array
    {
        $primary = $this->image ? product_image_url($this->image) : asset('images/placeholder-tire.svg');
        $extra = array_map(fn ($img) => product_image_url($img), $this->images ?? []);

        return array_values(array_unique(array_filter(array_merge([$primary], $extra))));
    }

    public function isUsed(): bool
    {
        return $this->condition === 'used';
    }

    public function conditionGradeLabel(): ?string
    {
        return match ($this->condition_grade) {
            'excellent' => 'Excellent',
            'very_good' => 'Very Good',
            'good' => 'Good',
            'fair' => 'Fair',
            default => null,
        };
    }

    public function vendorDisplayName(): string
    {
        return $this->vendor?->shop_name ?: $this->vendor?->name ?: 'Marketplace Vendor';
    }

    public function dotAgeYears(): ?int
    {
        if (! $this->dot_year) {
            return null;
        }

        return now()->year - (int) $this->dot_year;
    }

    public function averageRating(): ?float
    {
        $avg = $this->reviews()->where('type', 'product')->avg('rating');

        return $avg ? round((float) $avg, 1) : null;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Accept "225/45R17" or separate width/aspect/rim values.
     *
     * @return array{width: int, aspect_ratio: int, rim_diameter: int}
     */
    public static function parseTireSize(
        ?string $size = null,
        ?int $width = null,
        ?int $aspectRatio = null,
        ?int $rimDiameter = null,
    ): array {
        if ($size !== null && trim($size) !== '') {
            if (! preg_match('/^(\d{3})\/(\d{2})R(\d{2})$/i', trim($size), $matches)) {
                throw new InvalidArgumentException('Invalid tire size format. Use e.g. 225/45R17.');
            }

            return [
                'width' => (int) $matches[1],
                'aspect_ratio' => (int) $matches[2],
                'rim_diameter' => (int) $matches[3],
            ];
        }

        if ($width === null || $aspectRatio === null || $rimDiameter === null) {
            throw new InvalidArgumentException('Provide a tire size string or all three size components.');
        }

        return [
            'width' => $width,
            'aspect_ratio' => $aspectRatio,
            'rim_diameter' => $rimDiameter,
        ];
    }

    public function formattedSize(): string
    {
        return "{$this->width}/{$this->aspect_ratio}R{$this->rim_diameter}";
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeBySize(Builder $query, array $size): Builder
    {
        return $query
            ->where('width', $size['width'])
            ->where('aspect_ratio', $size['aspect_ratio'])
            ->where('rim_diameter', $size['rim_diameter']);
    }

    public function scopeInStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    public function scopeNewTires(Builder $query): Builder
    {
        return $query->where('condition', 'new');
    }

    public function scopeUsedTires(Builder $query): Builder
    {
        return $query->where('condition', 'used');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'vendor_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'sku' => $this->sku,
            'brand' => $this->brand?->name,
            'size' => $this->formattedSize(),
            'season' => $this->season,
            'is_active' => $this->is_active,
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->is_active && $this->stock > 0;
    }
}
