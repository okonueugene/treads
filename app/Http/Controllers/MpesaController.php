<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Payment;
use App\Models\MpesaTransaction;

class MpesaController extends Controller
{
    public function callback(Request $request)
    {
        // Handle Daraja callback
        Log::info('M-Pesa callback received', ['body' => $request->all()]);

        $data = $request->all();

        // Daraja callback structure may vary; attempt to parse common fields
        $body = $data['Body'] ?? $data;
        $stkCallback = $body['stkCallback'] ?? ($data['stkCallback'] ?? null);

        if ($stkCallback) {
            $checkoutRequestId = $stkCallback['CheckoutRequestID'] ?? null;
            $merchantRequestId = $stkCallback['MerchantRequestID'] ?? null;
            $resultCode = $stkCallback['ResultCode'] ?? null;
            $resultDesc = $stkCallback['ResultDesc'] ?? null;

            $mpesa = MpesaTransaction::firstOrCreate(
                ['checkout_request_id' => $checkoutRequestId],
                ['merchant_request_id' => $merchantRequestId, 'result_code' => $resultCode, 'result_desc' => $resultDesc, 'callback_data' => $data]
            );

            if ($resultCode === 0) {
                // Success — find callback metadata for receipt & amount
                $callbackMetadata = $stkCallback['CallbackMetadata']['Item'] ?? null;
                $receipt = null;
                $amount = null;
                $phone = null;

                if (is_array($callbackMetadata)) {
                    foreach ($callbackMetadata as $item) {
                        if (($item['Name'] ?? '') === 'MpesaReceiptNumber') {
                            $receipt = $item['Value'] ?? null;
                        }
                        if (($item['Name'] ?? '') === 'Amount') {
                            $amount = $item['Value'] ?? null;
                        }
                        if (($item['Name'] ?? '') === 'PhoneNumber') {
                            $phone = $item['Value'] ?? null;
                        }
                    }
                }

                $mpesa->update([
                    'receipt_number' => $receipt,
                    'result_code' => 0,
                    'result_desc' => $resultDesc,
                    'callback_data' => $data,
                    'transaction_status' => 'success',
                ]);

                // Prefer matching payment by checkout_request_id
                $payment = null;
                if ($checkoutRequestId) {
                    $payment = Payment::where('checkout_request_id', $checkoutRequestId)->where('status', 'initiated')->first();
                }

                // fallback to phone+amount matching for older payments
                if (! $payment) {
                    $payment = Payment::where('phone_number', $phone)->where('amount', $amount)->where('status', 'initiated')->latest()->first();
                }

                if ($payment) {
                    $payment->update(['status' => 'paid', 'transaction_code' => $receipt, 'paid_at' => now(), 'gateway_response' => $data]);
                    $mpesa->update(['payment_id' => $payment->id]);

                    // update related order status to 'paid'
                    try {
                        $order = $payment->order;
                        if ($order) {
                            $order->update(['status' => 'paid', 'payment_status' => 'paid']);
                        }
                    } catch (\Throwable $e) {
                        Log::error('Failed to update order status after mpesa callback', ['error' => $e->getMessage()]);
                    }
                }
            } else {
                $mpesa->update(['result_code' => $resultCode, 'result_desc' => $resultDesc, 'transaction_status' => 'failed', 'callback_data' => $data]);
            }
        } else {
            // Generic store
            MpesaTransaction::create(['callback_data' => $data, 'transaction_status' => 'initiated']);
        }

        return response()->json(['status' => 'ok']);
    }

    public function status(\App\Models\Payment $payment)
    {
        // Return basic payment status for frontend polling
        return response()->json([
            'payment_id' => $payment->id,
            'status' => $payment->status,
            'transaction_code' => $payment->transaction_code,
            'gateway_response' => $payment->gateway_response,
            'paid_at' => $payment->paid_at,
        ]);
    }

    public function initiate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'phone_number' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $order = \App\Models\Order::find($request->order_id);

        // create payment record
        $payment = Payment::create([
            'order_id' => $order->id,
            'method' => 'mpesa_express',
            'amount' => $request->amount,
            'phone_number' => $request->phone_number,
            'status' => 'initiated',
        ]);

        // Prepare Daraja credentials
        $consumerKey = env('MPESA_CONSUMER_KEY');
        $consumerSecret = env('MPESA_CONSUMER_SECRET');
        $shortcode = env('MPESA_SHORTCODE');
        $passkey = env('MPESA_PASSKEY');
        $callbackUrl = env('MPESA_CALLBACK_URL');
        $envMode = env('MPESA_ENVIRONMENT', 'sandbox');

        if (! $consumerKey || ! $consumerSecret || ! $shortcode || ! $passkey || ! $callbackUrl) {
            return response()->json(['error' => 'MPESA credentials not configured'], 500);
        }

        // Get OAuth token
        $authUrl = $envMode === 'production' ? 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials' : 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials';

        $tokenResp = Http::withBasicAuth($consumerKey, $consumerSecret)->get($authUrl);
        if (! $tokenResp->ok()) {
            Log::error('Failed to get mpesa token', ['resp' => $tokenResp->body()]);
            return response()->json(['error' => 'Failed to get MPESA token'], 500);
        }

        $access = $tokenResp->json()['access_token'] ?? null;
        if (! $access) {
            return response()->json(['error' => 'MPESA token missing'], 500);
        }

        // Prepare STK push payload
        $timestamp = now()->format('YmdHis');
        $password = base64_encode($shortcode.$passkey.$timestamp);
        $lipaUrl = $envMode === 'production' ? 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest' : 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';

        $payload = [
            'BusinessShortCode' => $shortcode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => (int) round($request->amount),
            'PartyA' => $request->phone_number,
            'PartyB' => $shortcode,
            'PhoneNumber' => $request->phone_number,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => 'ORDER-'.$order->id,
            'TransactionDesc' => 'Payment for order '.$order->id,
        ];

        $resp = Http::withToken($access)->post($lipaUrl, $payload);

        if (! $resp->ok()) {
            Log::error('STK push failed', ['resp' => $resp->body()]);
            $payment->update(['status' => 'failed', 'gateway_response' => $resp->body()]);
            return response()->json(['error' => 'STK push request failed'], 500);
        }

        $json = $resp->json();
        // store transaction reference
        $mpesa = MpesaTransaction::create([
            'payment_id' => $payment->id,
            'merchant_request_id' => $json['MerchantRequestID'] ?? null,
            'checkout_request_id' => $json['CheckoutRequestID'] ?? null,
            'result_code' => $json['ResponseCode'] ?? null,
            'result_desc' => $json['ResponseDescription'] ?? ($json['errorMessage'] ?? null),
            'callback_data' => $json,
            'transaction_status' => ($json['ResponseCode'] ?? null) === 0 ? 'initiated' : 'failed',
        ]);

        // update payment with initiated status and record checkout_request_id
        $payment->update(['status' => 'initiated', 'gateway_response' => $json, 'checkout_request_id' => $json['CheckoutRequestID'] ?? null]);

        return response()->json(['status' => 'initiated', 'checkout_request_id' => $json['CheckoutRequestID'] ?? null, 'payment_id' => $payment->id]);
    }
}
