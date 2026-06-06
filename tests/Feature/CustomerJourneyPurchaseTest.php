<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerJourneyPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_used_tire_details(): void
    {
        $product = Product::factory()->used()->create([
            'title' => 'Used Michelin Tire',
            'condition_grade' => 'excellent',
            'tread_depth_mm' => 7.5,
            'dot_year' => 2022,
        ]);

        $this->get(route('products.show', $product))
            ->assertOk()
            ->assertSee('Used Michelin Tire')
            ->assertSee('Used Tire Details')
            ->assertSee('Excellent')
            ->assertSee('7.5 mm');
    }

    public function test_buy_now_redirects_to_checkout(): void
    {
        $product = Product::factory()->create(['stock' => 5]);

        $this->post(route('cart.buy-now'), ['product_id' => $product->id])
            ->assertRedirect(route('checkout'));

        $this->assertEquals(1, app(CartService::class)->count());
    }

    public function test_authenticated_user_can_access_account_orders(): void
    {
        $user = User::factory()->create();
        Order::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('My Orders');
    }

    public function test_order_confirmation_page_is_accessible_after_placement(): void
    {
        $user = User::factory()->create();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'delivery_method' => 'home_delivery',
            'subtotal' => 10000,
            'tax' => 0,
            'shipping' => 0,
            'total' => 10000,
            'shipping_name' => 'Test User',
            'shipping_address' => '123 Road',
            'shipping_county' => 'Nairobi',
            'shipping_town' => 'Nairobi',
            'shipping_city' => 'Nairobi',
            'shipping_zip' => '',
            'payment_status' => 'unpaid',
        ]);

        session(['last_order_id' => $order->id]);

        $this->get(route('orders.confirmation', $order))
            ->assertOk()
            ->assertSee('Order Successfully Created')
            ->assertSee($order->order_number);
    }
}
