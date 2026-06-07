<?php

use App\Http\Controllers\Account\AddressController as AccountAddressController;
use App\Http\Controllers\Account\DashboardController as AccountDashboardController;
use App\Http\Controllers\Account\OrderController as AccountOrderController;
use App\Http\Controllers\Account\ReviewController as AccountReviewController;
use App\Http\Controllers\Account\TireRequestController as AccountTireRequestController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\FitmentController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SavedItemController;
use App\Http\Controllers\Vendor\DashboardController as VendorDashboardController;
use App\Http\Controllers\Vendor\ProductImportController;
use App\Models\Product;
use App\Services\SavedItemsService;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => view('home.index'))->name('home');

Route::get('/shop', fn () => view('shop.index'))->name('shop.index');
Route::redirect('/shop/sidebar', '/shop')->name('shop.sidebar');
Route::redirect('/shop/details', '/shop')->name('shop.details');
Route::redirect('/shop/cart', '/cart')->name('shop.cart');
Route::redirect('/fitment', '/#vehicle-search')->name('fitment.index');

Route::get('/products/{product}', function (Product $product, SavedItemsService $saved) {
    $product->load(['brand', 'category', 'vendor']);
    $relatedProducts = Product::query()
        ->active()
        ->inStock()
        ->where('id', '!=', $product->id)
        ->when($product->category_id, fn ($q) => $q->where('category_id', $product->category_id))
        ->with('brand')
        ->limit(4)
        ->get();

    return view('products.show', [
        'product' => $product,
        'relatedProducts' => $relatedProducts,
        'isSaved' => $saved->has($product->id),
    ]);
})->name('products.show');

Route::post('/saved/{product}', [SavedItemController::class, 'toggle'])->name('saved.toggle');

Route::post('/api/fitment/lookup', [FitmentController::class, 'lookup']);

Route::post('/payments/stripe/create', [\App\Http\Controllers\PaymentController::class, 'createStripeSession']);
Route::post('/payments/stripe/create-from-cart', [\App\Http\Controllers\PaymentController::class, 'createStripeSessionFromCart']);
Route::post('/payments/stripe/webhook', [\App\Http\Controllers\PaymentController::class, 'stripeWebhook']);

Route::post('/payments/mpesa/callback', [\App\Http\Controllers\MpesaController::class, 'callback']);
Route::post('/payments/mpesa/initiate', [\App\Http\Controllers\MpesaController::class, 'initiate']);

Route::redirect('/tire-search', '/shop');

Route::get('/checkout/success', fn () => view('checkout.success'))->name('checkout.success');
Route::get('/checkout/cancel', fn () => view('checkout.cancel'))->name('checkout.cancel');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::post('/cart/buy-now', [CartController::class, 'buyNow'])->name('cart.buy-now');
Route::patch('/cart/{productId}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{productId}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
Route::get('/orders/{order}/payment-status', [OrderController::class, 'paymentStatus'])->name('orders.payment-status');
Route::get('/orders/{order}/confirmation', [OrderController::class, 'confirmation'])->name('orders.confirmation');
Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
Route::post('/orders/{order}/confirm-receipt', [OrderController::class, 'confirmReceipt'])->name('orders.confirm-receipt');
Route::post('/orders/{order}/reviews', [ReviewController::class, 'store'])->middleware('auth')->name('orders.reviews.store');

Route::redirect('/dashboard', '/account')->name('dashboard');

Route::middleware(['auth', 'verified'])->prefix('account')->name('account.')->group(function () {
    Route::get('/', AccountDashboardController::class)->name('index');
    Route::get('/orders', [AccountOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [AccountOrderController::class, 'show'])->name('orders.show');
    Route::get('/addresses', [AccountAddressController::class, 'index'])->name('addresses.index');
    Route::post('/addresses', [AccountAddressController::class, 'store'])->name('addresses.store');
    Route::delete('/addresses/{address}', [AccountAddressController::class, 'destroy'])->name('addresses.destroy');
    Route::get('/tire-requests', [AccountTireRequestController::class, 'index'])->name('tire-requests.index');
    Route::get('/reviews', [AccountReviewController::class, 'index'])->name('reviews.index');
});

Route::get('/admin', function () {
    return view('admin.dashboard');
})->middleware(['auth'])->name('admin.dashboard');

Route::middleware(['auth'])->group(function () {
    Route::get('/admin/vendors/pending', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'index'])->name('admin.vendors.pending');
    Route::post('/admin/vendors/{user}/approve', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'approve'])->name('admin.vendors.approve');
    Route::post('/admin/vendors/{user}/reject', [\App\Http\Controllers\Admin\VendorApprovalController::class, 'reject'])->name('admin.vendors.reject');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('can:access-vendor-dashboard')->group(function () {
        Route::get('/vendor', VendorDashboardController::class)->name('vendor.dashboard');
        Route::post('/vendor/products/import', ProductImportController::class)->name('vendor.products.import');
    });
});

require __DIR__.'/auth.php';
