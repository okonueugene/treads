<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class PaymentController extends Controller
{
    public function createStripeSession(Request $request)
    {
        // Minimal placeholder: ensure stripe-php is installed and STRIPE_SECRET is set
        if (!class_exists('\Stripe\Stripe')) {
            return response()->json(['error' => 'Stripe SDK not installed. Run composer require stripe/stripe-php'], 501);
        }

        $lineItems = $request->input('line_items', []);

        \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'mode' => 'payment',
                'line_items' => $lineItems,
                'success_url' => url('/checkout/success?session_id={CHECKOUT_SESSION_ID}'),
                'cancel_url' => url('/checkout/cancel'),
            ]);

            return response()->json(['id' => $session->id]);
        } catch (\Throwable $e) {
            Log::error('Stripe session error: '.$e->getMessage());
            return response()->json(['error' => 'Failed to create Stripe session'], 500);
        }
    }

    public function createStripeSessionFromCart(\Illuminate\Http\Request $request, \App\Services\CartService $cart)
    {
        if (!class_exists('\Stripe\Stripe')) {
            return response()->json(['error' => 'Stripe SDK not installed. Run composer require stripe/stripe-php'], 501);
        }

        $lines = $cart->lines();

        if ($lines->isEmpty()) {
            return response()->json(['error' => 'Cart is empty'], 400);
        }

            // Create a pending order record (do not decrement stock yet)
            $order = \App\Models\Order::create([
                'user_id' => $request->user()?->id,
                'order_number' => 'TMP-'.strtoupper(\Illuminate\Support\Str::random(10)),
                'status' => 'pending',
                'subtotal' => $cart->subtotal(),
                'tax' => 0,
                'shipping' => 0,
                'total' => $cart->subtotal(),
                'shipping_name' => $request->input('shipping_name', 'Guest'),
                'shipping_address' => $request->input('shipping_address', ''),
                'shipping_city' => $request->input('shipping_city', ''),
                'shipping_state' => $request->input('shipping_state', ''),
                'shipping_zip' => $request->input('shipping_zip', ''),
                'shipping_phone' => $request->input('shipping_phone', ''),
                'notes' => $request->input('notes', null),
            ]);

            // Create order items snapshot
            foreach ($lines as $line) {
                $product = $line['product'];
                $vendor = $product->vendor;
                $lineTotal = $line['line_total'];
                $commissionRate = (float) ($vendor->commission_rate ?? 0);
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
                ]);
            }

            $lineItems = $lines->map(function ($line) {
                return [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $line['product']->title],
                        'unit_amount' => (int) round($line['product']->price * 100),
                    ],
                    'quantity' => $line['quantity'],
                ];
            })->values()->all();

            \Stripe\Stripe::setApiKey(config('services.stripe.secret'));

            try {
                $session = \Stripe\Checkout\Session::create([
                    'payment_method_types' => ['card'],
                    'mode' => 'payment',
                    'client_reference_id' => (string) $order->id,
                    'line_items' => $lineItems,
                    'success_url' => url('/checkout/success?session_id={CHECKOUT_SESSION_ID}'),
                    'cancel_url' => url('/checkout/cancel'),
                ]);

                // store session id on order for later verification
                $order->update(['stripe_session_id' => $session->id]);

                return response()->json(['id' => $session->id, 'order_id' => $order->id]);
            } catch (\Throwable $e) {
                Log::error('Stripe session error: '.$e->getMessage());
                return response()->json(['error' => 'Failed to create Stripe session'], 500);
            }
        }

    public function stripeWebhook(Request $request)
    {
        // Webhook handler: verify signature if endpoint secret configured and avoid double-processing
        if (!class_exists('\Stripe\Webhook')) {
            Log::warning('Stripe webhook received but SDK not installed');
            return response('SDK missing', 501);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        try {
            if ($endpointSecret) {
                $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            } else {
                $event = json_decode($payload);
            }

            $eventId = $event->id ?? null;

            // Record the event id to prevent double-processing. If already processed, return 200.
            if ($eventId) {
                $inserted = DB::table('stripe_events')->insertOrIgnore([
                    'event_id' => $eventId,
                    'order_id' => null,
                    'payload' => $payload,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                if ($inserted === 0) {
                    // Already processed
                    Log::info('Stripe webhook ignored duplicate event', ['event_id' => $eventId]);
                    return response('ok', 200);
                }
            }

            if (isset($event->type) && $event->type === 'checkout.session.completed') {
                $session = $event->data->object;
                // client_reference_id was set to local order id
                $orderId = $session->client_reference_id ?? null;
                $stripeSessionId = $session->id ?? null;

                if ($orderId) {
                    $order = \App\Models\Order::find($orderId);

                    if ($order && $order->payment_status !== 'paid') {
                        // mark paid and decrement stock
                        foreach ($order->items as $item) {
                            if ($item->product) {
                                $item->product->decrement('stock', $item->quantity);
                            }
                        }

                        $order->update([
                            'payment_status' => 'paid',
                            'status' => 'processing',
                            'stripe_session_id' => $stripeSessionId,
                            'payment_snapshot' => json_encode($session),
                        ]);

                        Log::info('Order marked paid via Stripe webhook', ['order_id' => $order->id]);
                    }

                    // link the processed event row to this order for auditing
                    if ($eventId) {
                        DB::table('stripe_events')->where('event_id', $eventId)->update(['order_id' => $order->id]);
                    }
                }
            }

            return response('ok', 200);
        } catch (\UnexpectedValueException $e) {
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return response('Invalid signature', 400);
        }
    }
}
