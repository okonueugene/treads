<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CartService;
use App\Services\SavedItemsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cart,
        private readonly SavedItemsService $saved,
    ) {}

    public function index(): View
    {
        return view('cart.index', [
            'lines' => $this->cart->lines(),
            'linesByVendor' => $this->cart->linesGroupedByVendor(),
            'subtotal' => $this->cart->subtotal(),
            'savedItems' => $this->saved->products(),
        ]);
    }

    public function buyNow(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()->active()->inStock()->findOrFail($validated['product_id']);

        $this->cart->clear();
        $this->cart->add($product, $validated['quantity'] ?? 1);

        return redirect()->route('checkout');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:99'],
        ]);

        $product = Product::query()->active()->inStock()->findOrFail($validated['product_id']);
        $this->cart->add($product, $validated['quantity'] ?? 1);

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, int $productId): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => ['required', 'integer', 'min:0', 'max:99'],
        ]);

        $this->cart->update($productId, $validated['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(int $productId): RedirectResponse
    {
        $this->cart->remove($productId);

        return back()->with('status', 'Item removed.');
    }
}
