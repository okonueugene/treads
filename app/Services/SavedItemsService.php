<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class SavedItemsService
{
    private const SESSION_KEY = 'saved_products';

    /** @return Collection<int, int> */
    public function ids(): Collection
    {
        return collect(session(self::SESSION_KEY, []));
    }

    public function toggle(Product $product): bool
    {
        $ids = $this->ids();

        if ($ids->contains($product->id)) {
            session([self::SESSION_KEY => $ids->reject(fn ($id) => $id === $product->id)->values()->all()]);

            return false;
        }

        session([self::SESSION_KEY => $ids->push($product->id)->unique()->values()->all()]);

        return true;
    }

    public function has(int $productId): bool
    {
        return $this->ids()->contains($productId);
    }

    /** @return Collection<int, Product> */
    public function products(): Collection
    {
        $ids = $this->ids();

        if ($ids->isEmpty()) {
            return collect();
        }

        return Product::query()
            ->with(['brand', 'vendor'])
            ->whereIn('id', $ids)
            ->get()
            ->sortBy(fn (Product $p) => $ids->search($p->id));
    }
}
