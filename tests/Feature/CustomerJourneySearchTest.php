<?php

namespace Tests\Feature;

use App\Models\FitmentData;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CustomerJourneySearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_includes_dual_search_experience(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Find Tires For Your Vehicle');
        $response->assertSee('Search By Tire Size');
    }

    public function test_fitment_route_redirects_to_home_vehicle_search(): void
    {
        $this->get('/fitment')->assertRedirect('/#vehicle-search');
    }

    public function test_shop_loads_products_from_size_query_string(): void
    {
        $product = Product::factory()->create([
            'title' => 'Journey Test Tire',
            'width' => 225,
            'aspect_ratio' => 45,
            'rim_diameter' => 17,
            'stock' => 5,
            'is_active' => true,
        ]);

        $response = $this->get(route('shop.index', ['size' => '225/45R17']));

        $response->assertOk();
        $response->assertSee('Journey Test Tire');
        $response->assertSee('225/45R17 Tires');
    }

    public function test_condition_filter_returns_only_matching_products(): void
    {
        $newTire = Product::factory()->create([
            'title' => 'Brand New Tire',
            'condition' => 'new',
            'width' => 225,
            'aspect_ratio' => 45,
            'rim_diameter' => 17,
        ]);

        Product::factory()->used()->create([
            'title' => 'Used Bargain Tire',
            'width' => 225,
            'aspect_ratio' => 45,
            'rim_diameter' => 17,
        ]);

        Livewire::test('tire-search', ['size' => '225/45R17'])
            ->set('conditions', ['new'])
            ->assertSee('Brand New Tire')
            ->assertDontSee('Used Bargain Tire');
    }

    public function test_tire_request_is_saved_when_no_results_for_size(): void
    {
        Livewire::test('tire-search', [
            'size' => '275/40R20',
            'width' => 275,
            'aspect_ratio' => 40,
            'rim_diameter' => 20,
        ])
            ->set('requestPhone', '0712345678')
            ->set('requestPreference', 'either')
            ->call('submitTireRequest')
            ->assertSet('requestSubmitted', '275/40R20');

        $this->assertDatabaseHas('tire_requests', [
            'phone' => '0712345678',
            'width' => 275,
            'aspect_ratio' => 40,
            'rim_diameter' => 20,
            'preference' => 'either',
            'status' => 'open',
        ]);
    }

    public function test_vehicle_size_finder_shows_recommended_sizes(): void
    {
        FitmentData::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year_from' => 2018,
            'year_to' => 2022,
            'width' => 205,
            'aspect_ratio' => 55,
            'rim_diameter' => 16,
            'fitment_type' => 'oem',
        ]);

        FitmentData::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year_from' => 2018,
            'year_to' => 2022,
            'width' => 225,
            'aspect_ratio' => 45,
            'rim_diameter' => 17,
            'fitment_type' => 'upgrade',
        ]);

        Livewire::test('vehicle-size-finder')
            ->set('make', 'Toyota')
            ->set('model', 'Corolla')
            ->set('year', 2020)
            ->call('search')
            ->assertSee('205/55R16')
            ->assertSee('225/45R17')
            ->assertSee('OEM');
    }

    public function test_compact_tire_search_redirects_to_shop_with_size(): void
    {
        Livewire::test('tire-search', ['compact' => true])
            ->set('width', 225)
            ->set('aspectRatio', 45)
            ->set('rimDiameter', 17)
            ->call('searchBySize')
            ->assertRedirect(route('shop.index', ['size' => '225/45R17']));
    }

    public function test_shop_shows_vehicle_size_picker_when_vehicle_params_without_size(): void
    {
        FitmentData::create([
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year_from' => 2018,
            'year_to' => 2022,
            'width' => 205,
            'aspect_ratio' => 55,
            'rim_diameter' => 16,
            'fitment_type' => 'oem',
        ]);

        $response = $this->get(route('shop.index', [
            'make' => 'Toyota',
            'model' => 'Corolla',
            'year' => 2020,
        ]));

        $response->assertOk();
        $response->assertSee('Select a tire size for Toyota Corolla 2020');
        $response->assertSee('205/55R16');
    }
}
