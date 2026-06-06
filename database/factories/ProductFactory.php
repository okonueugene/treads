<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'vendor_id' => User::factory(),
            'brand_id' => Brand::factory(),
            'sku' => fake()->unique()->bothify('SKU-####'),
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'price' => fake()->randomFloat(2, 5000, 50000),
            'stock' => fake()->numberBetween(1, 50),
            'condition' => 'new',
            'width' => 225,
            'aspect_ratio' => 45,
            'rim_diameter' => 17,
            'season' => 'all-season',
            'is_active' => true,
            'is_verified' => false,
            'sold_count' => 0,
        ];
    }

    public function used(): static
    {
        return $this->state(fn () => [
            'condition' => 'used',
            'condition_grade' => fake()->randomElement(['excellent', 'very_good', 'good']),
            'tread_depth_mm' => fake()->randomFloat(1, 3, 8),
            'dot_year' => fake()->numberBetween(2019, 2024),
            'dot_week' => fake()->numberBetween(1, 52),
        ]);
    }
}
