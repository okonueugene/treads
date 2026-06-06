<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Order $order): RedirectResponse
    {
        abort_unless($order->user_id === $request->user()?->id, 403);

        $validated = $request->validate([
            'product_id' => ['nullable', 'exists:products,id'],
            'vendor_id' => ['nullable', 'exists:users,id'],
            'type' => ['required', 'in:product,vendor'],
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::query()->create([
            ...$validated,
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
        ]);

        return back()->with('status', 'Thank you for your review.');
    }
}
