<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CartService;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly CartService $cart,
    ) {}

    public function checkout(Request $request): View|RedirectResponse
    {
        if ($this->cart->lines()->isEmpty()) {
            return redirect()->route('cart.index')->with('status', 'Your cart is empty.');
        }

        $defaultAddress = $request->user()?->addresses()->where('is_default', true)->first()
            ?? $request->user()?->addresses()->latest()->first();

        return view('orders.checkout', [
            'lines' => $this->cart->lines(),
            'linesByVendor' => $this->cart->linesGroupedByVendor(),
            'subtotal' => $this->cart->subtotal(),
            'counties' => config('marketplace.counties'),
            'defaultAddress' => $defaultAddress,
            'paybill' => config('marketplace.mpesa_paybill'),
            'bank' => config('marketplace.bank_transfer'),
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'shipping_name' => ['required', 'string', 'max:255'],
            'shipping_phone' => ['required', 'string', 'max:30'],
            'shipping_county' => ['required', 'string', 'max:100'],
            'shipping_town' => ['required', 'string', 'max:100'],
            'shipping_address' => ['required', 'string', 'max:255'],
            'shipping_landmark' => ['nullable', 'string', 'max:255'],
            'delivery_method' => ['required', 'in:pickup,home_delivery'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', 'in:mpesa_express,mpesa_manual,bank_transfer,stripe'],
            'payment_phone' => ['nullable', 'string', 'max:30'],
            'transaction_code' => ['nullable', 'string', 'max:50'],
        ]);

        try {
            $order = $this->orders->place(
                $request->user(),
                [
                    'name' => $validated['shipping_name'],
                    'phone' => $validated['shipping_phone'],
                    'county' => $validated['shipping_county'],
                    'town' => $validated['shipping_town'],
                    'address' => $validated['shipping_address'],
                    'landmark' => $validated['shipping_landmark'] ?? null,
                    'city' => $validated['shipping_town'],
                    'zip' => '',
                    'delivery_method' => $validated['delivery_method'],
                    'notes' => $validated['notes'] ?? null,
                ],
            );
        } catch (\RuntimeException $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            return back()->withErrors(['cart' => $e->getMessage()]);
        }

        session(['last_order_id' => $order->id]);

        if (in_array($validated['payment_method'], ['mpesa_manual', 'bank_transfer'], true)) {
            $this->orders->recordManualPayment(
                $order,
                $validated['payment_method'],
                $validated['transaction_code'] ?? null,
                $validated['payment_phone'] ?? null,
            );

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                    'total' => $order->total,
                    'next' => 'confirmation',
                ]);
            }

            return redirect()
                ->route('orders.confirmation', $order)
                ->with('status', 'Order placed. Payment is being verified.');
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'order_id' => $order->id,
                'order_number' => $order->order_number,
                'total' => $order->total,
                'next' => $validated['payment_method'],
            ]);
        }

        return redirect()
            ->route('orders.confirmation', $order)
            ->with('status', 'Order placed successfully.');
    }

    public function paymentStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorizeOrder($request, $order);

        return response()->json([
            'payment_status' => $order->payment_status,
            'order_status' => $order->status,
        ]);
    }

    public function confirmation(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);
        $order->load(['items.vendor', 'payments']);

        return view('orders.confirmation', compact('order'));
    }

    public function show(Request $request, Order $order): View
    {
        $this->authorizeOrder($request, $order);

        $order->load(['items.product', 'items.vendor', 'payments', 'reviews']);

        return view('orders.show', [
            'order' => $order,
            'fromAccount' => false,
        ]);
    }

    public function confirmReceipt(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($request, $order);

        if (! in_array($order->status, ['shipped', 'delivered', 'processing', 'paid'], true)) {
            return back()->withErrors(['order' => 'This order cannot be confirmed yet.']);
        }

        $this->orders->confirmReceipt($order);

        return back()->with('status', 'Receipt confirmed. Thank you!');
    }

    protected function authorizeOrder(Request $request, Order $order): void
    {
        if ($request->user()?->id === $order->user_id) {
            return;
        }

        if (session('last_order_id') === $order->id) {
            return;
        }

        abort(403);
    }
}
