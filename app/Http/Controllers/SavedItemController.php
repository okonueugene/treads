<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\SavedItemsService;
use Illuminate\Http\RedirectResponse;

class SavedItemController extends Controller
{
    public function __construct(
        private readonly SavedItemsService $saved,
    ) {}

    public function toggle(Product $product): RedirectResponse
    {
        $added = $this->saved->toggle($product);

        return back()->with('status', $added ? 'Saved for later.' : 'Removed from saved items.');
    }
}
