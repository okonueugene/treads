<?php

namespace Tests\Feature;

use App\Models\FitmentData;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FitmentLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_fitment_lookup_returns_products()
    {
        // create product matching size
        $product = Product::factory()->create([
            'title' => 'Test Tire',
            'width' => 205,
            'aspect_ratio' => 55,
            'rim_diameter' => 16,
            'price' => 100,
            'stock' => 10,
            'is_active' => true,
        ]);

        // create fitment data for a vehicle
        FitmentData::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year_from' => 2015,
            'year_to' => 2020,
            'width' => 205,
            'aspect_ratio' => 55,
            'rim_diameter' => 16,
        ]);

        $response = $this->post('/api/fitment/lookup', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2016,
        ]);

        $response->assertStatus(200);
        $response->assertSee('Test Tire');
    }
}
