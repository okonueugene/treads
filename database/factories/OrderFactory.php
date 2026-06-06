<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'order_number' => Order::generateOrderNumber(),
            'status' => 'pending',
            'delivery_method' => 'home_delivery',
            'subtotal' => 10000,
            'tax' => 0,
            'shipping' => 0,
            'total' => 10000,
            'shipping_name' => fake()->name(),
            'shipping_address' => fake()->streetAddress(),
            'shipping_county' => 'Nairobi',
            'shipping_town' => 'Nairobi',
            'shipping_city' => 'Nairobi',
            'shipping_zip' => '',
            'payment_status' => 'unpaid',
        ];
    }
}
