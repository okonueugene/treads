<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        private readonly CartService $cart,
    ) {}

    /**
     * @param  array<string, mixed>  $shipping
     */
    public function place(?User $user, array $shipping, float $tax = 0, float $shippingCost = 0): Order
    {
        $lines = $this->cart->lines();

        if ($lines->isEmpty()) {
            throw new \RuntimeException('Cart is empty.');
        }

        foreach ($lines as $line) {
            if ($line['quantity'] > $line['product']->stock) {
                throw new \RuntimeException("Insufficient stock for {$line['product']->title}.");
            }
        }

        return DB::transaction(function () use ($user, $shipping, $tax, $shippingCost, $lines) {
            $subtotal = $this->cart->subtotal();
            $total = round($subtotal + $tax + $shippingCost, 2);

            $order = Order::query()->create([
                'user_id' => $user?->id,
                'order_number' => Order::generateOrderNumber(),
                'status' => 'pending',
                'delivery_method' => $shipping['delivery_method'] ?? 'home_delivery',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping' => $shippingCost,
                'total' => $total,
                'shipping_name' => $shipping['name'],
                'shipping_address' => $shipping['address'],
                'shipping_county' => $shipping['county'] ?? null,
                'shipping_town' => $shipping['town'] ?? null,
                'shipping_landmark' => $shipping['landmark'] ?? null,
                'shipping_city' => $shipping['city'] ?? $shipping['town'] ?? '',
                'shipping_state' => $shipping['state'] ?? null,
                'shipping_zip' => $shipping['zip'] ?? '',
                'shipping_phone' => $shipping['phone'] ?? null,
                'notes' => $shipping['notes'] ?? null,
                'payment_status' => 'unpaid',
            ]);

            foreach ($lines as $line) {
                /** @var Product $product */
                $product = $line['product'];
                $vendor = $product->vendor;
                $lineTotal = $line['line_total'];
                $commissionRate = (float) $vendor->commission_rate;
                $commissionAmount = round($lineTotal * ($commissionRate / 100), 2);

                $order->items()->create([
                    'product_id' => $product->id,
                    'vendor_id' => $vendor->id,
                    'product_title' => $product->title,
                    'product_sku' => $product->sku,
                    'quantity' => $line['quantity'],
                    'unit_price' => $product->price,
                    'line_total' => $lineTotal,
                    'commission_rate' => $commissionRate,
                    'commission_amount' => $commissionAmount,
                    'vendor_status' => 'pending',
                ]);

                $product->decrement('stock', $line['quantity']);
            }

            $this->cart->clear();

            return $order->load('items');
        });
    }

    public function recordManualPayment(Order $order, string $method, ?string $transactionCode = null, ?string $phone = null): Payment
    {
        $order->update(['payment_status' => 'processing']);

        return Payment::query()->create([
            'order_id' => $order->id,
            'method' => $method,
            'amount' => $order->total,
            'phone_number' => $phone,
            'transaction_code' => $transactionCode,
            'status' => 'processing',
        ]);
    }

    public function confirmReceipt(Order $order): void
    {
        $order->update([
            'status' => 'delivered',
            'delivered_at' => $order->delivered_at ?? now(),
            'receipt_confirmed_at' => now(),
        ]);

        $order->items()->update(['vendor_status' => 'delivered']);
    }
}
