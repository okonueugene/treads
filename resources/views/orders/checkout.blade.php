<x-app-layout>
    <x-slot name="header">
        <x-page-header title="Checkout" subtitle="Shipping and payment" class="mb-0" />
    </x-slot>

    <div class="page-container py-8" x-data="{ paymentMethod: 'mpesa_express', deliveryMethod: 'home_delivery' }">
        <div class="mb-8 flex items-center gap-4">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">1</span>
                <span class="text-sm font-medium text-white">Shipping</span>
            </div>
            <div class="h-px flex-1 bg-slate-700"></div>
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-500 text-sm font-bold text-white">2</span>
                <span class="text-sm font-medium text-white">Payment</span>
            </div>
        </div>

        <div class="grid gap-8 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-2">
                <form id="checkout-form" class="card space-y-5 p-6" onsubmit="return false;">
                    @csrf
                    <h3 class="text-lg font-semibold text-white">Shipping Information</h3>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <x-input-label for="shipping_name" value="Full name" />
                            <x-text-input id="shipping_name" name="shipping_name" class="mt-1 block w-full" :value="old('shipping_name', $defaultAddress?->name ?? auth()->user()?->name)" required />
                        </div>
                        <div>
                            <x-input-label for="shipping_phone" value="Phone" />
                            <x-text-input id="shipping_phone" name="shipping_phone" class="mt-1 block w-full" :value="old('shipping_phone', $defaultAddress?->phone)" required />
                        </div>
                        <div>
                            <x-input-label for="shipping_county" value="County" />
                            <select id="shipping_county" name="shipping_county" class="input-field mt-1 block w-full" required>
                                <option value="">Select county</option>
                                @foreach ($counties as $county)
                                    <option value="{{ $county }}" @selected(old('shipping_county', $defaultAddress?->county) === $county)>{{ $county }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <x-input-label for="shipping_town" value="Town" />
                            <x-text-input id="shipping_town" name="shipping_town" class="mt-1 block w-full" :value="old('shipping_town', $defaultAddress?->town)" required />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="shipping_address" value="Address" />
                            <x-text-input id="shipping_address" name="shipping_address" class="mt-1 block w-full" :value="old('shipping_address', $defaultAddress?->address)" required />
                        </div>
                        <div class="sm:col-span-2">
                            <x-input-label for="shipping_landmark" value="Landmark (optional)" />
                            <x-text-input id="shipping_landmark" name="shipping_landmark" class="mt-1 block w-full" :value="old('shipping_landmark', $defaultAddress?->landmark)" />
                        </div>
                    </div>

                    <fieldset>
                        <legend class="mb-2 text-sm font-medium text-slate-300">Delivery method</legend>
                        <div class="flex flex-wrap gap-4">
                            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                                <input type="radio" name="delivery_method" value="home_delivery" x-model="deliveryMethod" checked class="text-brand-500">
                                Home delivery
                            </label>
                            <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                                <input type="radio" name="delivery_method" value="pickup" x-model="deliveryMethod" class="text-brand-500">
                                Vendor pickup
                            </label>
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label for="notes" value="Order notes" />
                        <textarea id="notes" name="notes" rows="2" class="input-field mt-1 block w-full">{{ old('notes') }}</textarea>
                    </div>

                    <div class="border-t border-slate-700 pt-5">
                        <h3 class="text-lg font-semibold text-white">Payment Method</h3>
                        <div class="mt-3 space-y-2">
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-700 p-3" :class="paymentMethod === 'mpesa_express' && 'border-brand-500/50 bg-brand-500/5'">
                                <input type="radio" name="payment_method" value="mpesa_express" x-model="paymentMethod" class="mt-1 text-brand-500">
                                <span>
                                    <span class="font-medium text-white">M-Pesa Express</span>
                                    <span class="mt-1 block text-sm text-slate-400">STK push to your phone</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-700 p-3" :class="paymentMethod === 'mpesa_manual' && 'border-brand-500/50 bg-brand-500/5'">
                                <input type="radio" name="payment_method" value="mpesa_manual" x-model="paymentMethod" class="mt-1 text-brand-500">
                                <span>
                                    <span class="font-medium text-white">M-Pesa Paybill</span>
                                    <span class="mt-1 block text-sm text-slate-400">Pay manually and enter transaction code</span>
                                </span>
                            </label>
                            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-slate-700 p-3" :class="paymentMethod === 'bank_transfer' && 'border-brand-500/50 bg-brand-500/5'">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="paymentMethod" class="mt-1 text-brand-500">
                                <span>
                                    <span class="font-medium text-white">Bank transfer</span>
                                    <span class="mt-1 block text-sm text-slate-400">Transfer and submit reference</span>
                                </span>
                            </label>
                        </div>

                        <div class="mt-4 space-y-4" x-show="paymentMethod === 'mpesa_express'" x-cloak>
                            <div>
                                <x-input-label for="payment_phone" value="M-Pesa phone number" />
                                <x-text-input id="payment_phone" name="payment_phone" class="mt-1 block w-full" placeholder="0712345678" />
                            </div>
                        </div>

                        <div class="mt-4 space-y-3 rounded-lg border border-slate-700 bg-slate-800/30 p-4" x-show="paymentMethod === 'mpesa_manual'" x-cloak>
                            <p class="text-sm text-slate-300">Pay via M-Pesa Paybill:</p>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <dt class="text-slate-400">Business number</dt><dd class="text-white">{{ $paybill['business_number'] }}</dd>
                                <dt class="text-slate-400">Account</dt><dd class="text-white">{{ $paybill['account_prefix'] }}-ORDER</dd>
                                <dt class="text-slate-400">Amount</dt><dd class="text-white">{{ format_kes($subtotal) }}</dd>
                            </dl>
                            <div>
                                <x-input-label for="transaction_code_mpesa" value="M-Pesa transaction code" />
                                <x-text-input id="transaction_code_mpesa" class="mt-1 block w-full" placeholder="e.g. QHK7X9ABC1" />
                            </div>
                        </div>

                        <div class="mt-4 space-y-3 rounded-lg border border-slate-700 bg-slate-800/30 p-4" x-show="paymentMethod === 'bank_transfer'" x-cloak>
                            <p class="text-sm text-slate-300">Bank transfer details:</p>
                            <dl class="grid grid-cols-2 gap-2 text-sm">
                                <dt class="text-slate-400">Bank</dt><dd class="text-white">{{ $bank['bank_name'] }}</dd>
                                <dt class="text-slate-400">Account name</dt><dd class="text-white">{{ $bank['account_name'] }}</dd>
                                <dt class="text-slate-400">Account number</dt><dd class="text-white">{{ $bank['account_number'] }}</dd>
                                <dt class="text-slate-400">Branch</dt><dd class="text-white">{{ $bank['branch'] }}</dd>
                                <dt class="text-slate-400">Amount</dt><dd class="text-white">{{ format_kes($subtotal) }}</dd>
                            </dl>
                            <div>
                                <x-input-label for="transaction_code_bank" value="Transfer reference" />
                                <x-text-input id="transaction_code_bank" class="mt-1 block w-full" placeholder="Bank reference number" />
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap gap-3 pt-2">
                        <x-primary-button type="button" id="place-order-btn">Place order & pay</x-primary-button>
                        <a href="{{ route('cart.index') }}" class="btn-secondary">Back to cart</a>
                    </div>
                </form>
            </div>

            <div class="lg:sticky lg:top-24 lg:self-start">
                <div class="card space-y-4 p-6">
                    <h3 class="text-lg font-semibold text-white">Order Summary</h3>
                    @foreach ($linesByVendor as $vendorId => $vendorLines)
                        <div class="border-b border-slate-700 pb-3">
                            <p class="mb-2 text-xs font-medium uppercase tracking-wider text-slate-500">{{ $vendorLines->first()['product']->vendorDisplayName() }}</p>
                            @foreach ($vendorLines as $line)
                                <div class="flex justify-between gap-2 py-1 text-sm">
                                    <span class="truncate text-slate-300">{{ $line['product']->title }} ×{{ $line['quantity'] }}</span>
                                    <span class="text-white">{{ format_kes($line['line_total']) }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="flex justify-between border-t border-slate-700 pt-4">
                        <span class="font-semibold text-white">Total</span>
                        <span class="text-xl font-bold text-white">{{ format_kes($subtotal) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        async function postJson(url, body) {
            const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const res = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify(body || {}),
            });
            return res.json();
        }

        document.getElementById('place-order-btn').addEventListener('click', async function () {
            const form = document.getElementById('checkout-form');
            const paymentMethod = form.querySelector('[name="payment_method"]:checked').value;
            const payload = {
                shipping_name: form.querySelector('[name="shipping_name"]').value,
                shipping_phone: form.querySelector('[name="shipping_phone"]').value,
                shipping_county: form.querySelector('[name="shipping_county"]').value,
                shipping_town: form.querySelector('[name="shipping_town"]').value,
                shipping_address: form.querySelector('[name="shipping_address"]').value,
                shipping_landmark: form.querySelector('[name="shipping_landmark"]').value,
                delivery_method: form.querySelector('[name="delivery_method"]:checked').value,
                notes: form.querySelector('[name="notes"]').value,
                payment_method: paymentMethod,
                payment_phone: form.querySelector('#payment_phone')?.value || form.querySelector('[name="shipping_phone"]').value,
                transaction_code: paymentMethod === 'mpesa_manual'
                    ? document.getElementById('transaction_code_mpesa')?.value
                    : document.getElementById('transaction_code_bank')?.value,
            };

            const orderResp = await postJson('{{ route('orders.store') }}', payload);
            if (orderResp.error) {
                alert(orderResp.error);
                return;
            }

            if (orderResp.next === 'mpesa_express') {
                const mpesaResp = await postJson('/payments/mpesa/initiate', {
                    order_id: orderResp.order_id,
                    phone_number: payload.payment_phone,
                    amount: orderResp.total,
                });
                if (mpesaResp.error) {
                    alert('Failed to initiate M-Pesa: ' + mpesaResp.error);
                    window.location = '/orders/' + orderResp.order_id + '/confirmation';
                    return;
                }
                alert('M-Pesa STK push sent. Complete payment on your phone.');
            }

            window.location = '/orders/' + orderResp.order_id + '/confirmation';
        });
    </script>
</x-app-layout>
