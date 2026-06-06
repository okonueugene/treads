<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CartService
{
    private const SESSION_KEY = 'cart';

    /** @return Collection<int, array{product_id: int, quantity: int}> */
    public function items(): Collection
    {
        return collect(session(self::SESSION_KEY, []));
    }

    public function add(Product $product, int $quantity = 1): void
    {
        $cart = $this->items()->keyBy('product_id');
        $existing = $cart->get($product->id);

        if ($existing) {
            $cart->put($product->id, [
                'product_id' => $product->id,
                'quantity' => $existing['quantity'] + $quantity,
            ]);
        } else {
            $cart->put($product->id, [
                'product_id' => $product->id,
                'quantity' => $quantity,
            ]);
        }

        session([self::SESSION_KEY => $cart->values()->all()]);
    }

    public function update(int $productId, int $quantity): void
    {
        $cart = $this->items()->keyBy('product_id');

        if ($quantity <= 0) {
            $cart->forget($productId);
        } else {
            $cart->put($productId, [
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        }

        session([self::SESSION_KEY => $cart->values()->all()]);
    }

    public function remove(int $productId): void
    {
        $this->update($productId, 0);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /** @return Collection<int, array{product: Product, quantity: int, line_total: float}> */
    public function lines(): Collection
    {
        $productIds = $this->items()->pluck('product_id');

        if ($productIds->isEmpty()) {
            return collect();
        }

        $products = Product::query()
            ->with(['brand', 'vendor'])
            ->whereIn('id', $productIds)
            ->get()
            ->keyBy('id');

        return $this->items()->map(function (array $item) use ($products) {
            $product = $products->get($item['product_id']);

            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => $item['quantity'],
                'line_total' => round($product->price * $item['quantity'], 2),
            ];
        })->filter();
    }

    public function subtotal(): float
    {
        return round($this->lines()->sum('line_total'), 2);
    }

    public function count(): int
    {
        return (int) $this->items()->sum('quantity');
    }

    /**
     * @return Collection<int, Collection<int, array{product: Product, quantity: int, line_total: float}>>
     */
    public function linesGroupedByVendor(): Collection
    {
        return $this->lines()->groupBy(fn (array $line) => $line['product']->vendor_id);
    }
}
