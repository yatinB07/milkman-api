<?php

use App\Http\Controllers\Api\V1\Admin\BannerController;
use App\Http\Controllers\Api\V1\Admin\CashCollectionController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
use App\Http\Controllers\Api\V1\Admin\CouponController;
use App\Http\Controllers\Api\V1\Admin\CustomerAddressController;
use App\Http\Controllers\Api\V1\Admin\CustomerController;
use App\Http\Controllers\Api\V1\Admin\CustomerNotificationController;
use App\Http\Controllers\Api\V1\Admin\DeliveryOptionController;
use App\Http\Controllers\Api\V1\Admin\FaqController;
use App\Http\Controllers\Api\V1\Admin\FavoriteController;
use App\Http\Controllers\Api\V1\Admin\MilkDataController;
use App\Http\Controllers\Api\V1\Admin\OrderController;
use App\Http\Controllers\Api\V1\Admin\OrderItemController;
use App\Http\Controllers\Api\V1\Admin\PageController;
use App\Http\Controllers\Api\V1\Admin\PaymentMethodController;
use App\Http\Controllers\Api\V1\Admin\PayoutRequestController;
use App\Http\Controllers\Api\V1\Admin\ProductController;
use App\Http\Controllers\Api\V1\Admin\ProductImageController;
use App\Http\Controllers\Api\V1\Admin\ProductVariantController;
use App\Http\Controllers\Api\V1\Admin\RiderController;
use App\Http\Controllers\Api\V1\Admin\RiderNotificationController;
use App\Http\Controllers\Api\V1\Admin\SettingController;
use App\Http\Controllers\Api\V1\Admin\StoreCategoryController;
use App\Http\Controllers\Api\V1\Admin\StoreController;
use App\Http\Controllers\Api\V1\Admin\StoreGalleryImageController;
use App\Http\Controllers\Api\V1\Admin\StoreNotificationController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionOrderController;
use App\Http\Controllers\Api\V1\Admin\SubscriptionOrderItemController;
use App\Http\Controllers\Api\V1\Admin\TimeSlotController;
use App\Http\Controllers\Api\V1\Admin\WalletTransactionController;
use App\Http\Controllers\Api\V1\Admin\ZoneController;
use App\Http\Controllers\Api\V1\Auth\IdentityAuthController;
use App\Http\Controllers\Api\V1\Catalog\PublicCatalogController;
use App\Http\Controllers\Api\V1\Customer\CustomerAddressController as CustomerAddressApiController;
use App\Http\Controllers\Api\V1\Customer\CustomerCartController;
use App\Http\Controllers\Api\V1\Customer\CustomerCouponController;
use App\Http\Controllers\Api\V1\Customer\CustomerFavoriteController;
use App\Http\Controllers\Api\V1\Customer\CustomerHomeController;
use App\Http\Controllers\Api\V1\Customer\CustomerNotificationController as CustomerNotificationApiController;
use App\Http\Controllers\Api\V1\Customer\CustomerOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerPaymentMethodController;
use App\Http\Controllers\Api\V1\Customer\CustomerProductController;
use App\Http\Controllers\Api\V1\Customer\CustomerProfileController;
use App\Http\Controllers\Api\V1\Customer\CustomerStoreAvailabilityController;
use App\Http\Controllers\Api\V1\Customer\CustomerStoreController;
use App\Http\Controllers\Api\V1\Customer\CustomerSubscriptionOrderController;
use App\Http\Controllers\Api\V1\Customer\CustomerWalletController;
use App\Http\Controllers\Api\V1\HealthController;
use App\Http\Controllers\Api\V1\Store\StoreDashboardController;
use App\Http\Controllers\Api\V1\Store\StoreProductController;
use App\Http\Controllers\Api\V1\Store\StoreProductVariantController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('health', HealthController::class)->name('health');

    Route::prefix('public')->name('public.')->group(function (): void {
        Route::get('categories', [PublicCatalogController::class, 'categories'])->name('categories.index');
        Route::get('stores', [PublicCatalogController::class, 'stores'])->name('stores.index');
        Route::get('stores/{store}', [PublicCatalogController::class, 'store'])
            ->whereNumber('store')
            ->name('stores.show');
        Route::get('stores/{store}/products', [PublicCatalogController::class, 'products'])
            ->whereNumber('store')
            ->name('stores.products.index');
    });

    Route::prefix('admin')
        ->middleware('auth:sanctum')
        ->name('admin.')
        ->group(function (): void {
            Route::apiResource('banners', BannerController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('categories', CategoryController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('customers', CustomerController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('customer-addresses', CustomerAddressController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('customer-notifications', CustomerNotificationController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('favorites', FavoriteController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('wallet-transactions', WalletTransactionController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('payout-requests', PayoutRequestController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('cash-collections', CashCollectionController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('orders', OrderController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('order-items', OrderItemController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('subscription-orders', SubscriptionOrderController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('subscription-order-items', SubscriptionOrderItemController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('stores', StoreController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('riders', RiderController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('store-categories', StoreCategoryController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('products', ProductController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('product-variants', ProductVariantController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('product-images', ProductImageController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('store-gallery-images', StoreGalleryImageController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('store-notifications', StoreNotificationController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('rider-notifications', RiderNotificationController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('delivery-options', DeliveryOptionController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('time-slots', TimeSlotController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('coupons', CouponController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('faqs', FaqController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('pages', PageController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('payment-methods', PaymentMethodController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('zones', ZoneController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('settings', SettingController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('milk-data', MilkDataController::class)
                ->parameters(['milk-data' => 'milkData'])
                ->only(['index', 'show', 'store', 'update', 'destroy']);
        });

    Route::prefix('customer')
        ->middleware('auth:sanctum')
        ->name('customer.')
        ->group(function (): void {
            Route::get('home', [CustomerHomeController::class, 'show'])
                ->name('home.show');
            Route::get('profile', [CustomerProfileController::class, 'show'])
                ->name('profile.show');
            Route::put('profile', [CustomerProfileController::class, 'update'])
                ->name('profile.update');
            Route::apiResource('addresses', CustomerAddressApiController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::get('stores', [CustomerStoreController::class, 'index'])
                ->name('stores.index');
            Route::get('stores/{store}', [CustomerStoreController::class, 'show'])
                ->whereNumber('store')
                ->name('stores.show');
            Route::get('stores/{store}/products', [CustomerProductController::class, 'index'])
                ->whereNumber('store')
                ->name('stores.products.index');
            Route::get('products/{product}', [CustomerProductController::class, 'show'])
                ->whereNumber('product')
                ->name('products.show');
            Route::get('stores/{store}/delivery-options', [CustomerStoreAvailabilityController::class, 'deliveryOptions'])
                ->whereNumber('store')
                ->name('stores.delivery-options.index');
            Route::get('stores/{store}/time-slots', [CustomerStoreAvailabilityController::class, 'timeSlots'])
                ->whereNumber('store')
                ->name('stores.time-slots.index');
            Route::get('stores/{store}/cart-data', [CustomerCartController::class, 'show'])
                ->whereNumber('store')
                ->name('stores.cart-data.show');
            Route::get('stores/{store}/coupons', [CustomerCouponController::class, 'index'])
                ->whereNumber('store')
                ->name('stores.coupons.index');
            Route::post('coupons/check', [CustomerCouponController::class, 'check'])
                ->name('coupons.check');
            Route::get('favorites', [CustomerFavoriteController::class, 'index'])
                ->name('favorites.index');
            Route::post('favorites/toggle', [CustomerFavoriteController::class, 'toggle'])
                ->name('favorites.toggle');
            Route::get('notifications', [CustomerNotificationApiController::class, 'index'])
                ->name('notifications.index');
            Route::get('payment-methods', [CustomerPaymentMethodController::class, 'index'])
                ->name('payment-methods.index');
            Route::get('wallet-transactions', [CustomerWalletController::class, 'index'])
                ->name('wallet-transactions.index');
            Route::post('wallet/top-ups', [CustomerWalletController::class, 'topUp'])
                ->name('wallet.top-ups.store');
            Route::get('orders', [CustomerOrderController::class, 'index'])
                ->name('orders.index');
            Route::post('orders', [CustomerOrderController::class, 'store'])
                ->name('orders.store');
            Route::post('orders/{order}/rating', [CustomerOrderController::class, 'rate'])
                ->whereNumber('order')
                ->name('orders.rating.store');
            Route::get('orders/{order}', [CustomerOrderController::class, 'show'])
                ->whereNumber('order')
                ->name('orders.show');
            Route::get('subscription-orders', [CustomerSubscriptionOrderController::class, 'index'])
                ->name('subscription-orders.index');
            Route::post('subscription-orders', [CustomerSubscriptionOrderController::class, 'store'])
                ->name('subscription-orders.store');
            Route::post('subscription-orders/{subscriptionOrder}/rating', [CustomerSubscriptionOrderController::class, 'rate'])
                ->whereNumber('subscriptionOrder')
                ->name('subscription-orders.rating.store');
            Route::post('subscription-orders/{subscriptionOrder}/items/{item}/skip', [CustomerSubscriptionOrderController::class, 'skip'])
                ->whereNumber(['subscriptionOrder', 'item'])
                ->name('subscription-orders.items.skip');
            Route::post('subscription-orders/{subscriptionOrder}/items/{item}/extend', [CustomerSubscriptionOrderController::class, 'extend'])
                ->whereNumber(['subscriptionOrder', 'item'])
                ->name('subscription-orders.items.extend');
            Route::get('subscription-orders/{subscriptionOrder}', [CustomerSubscriptionOrderController::class, 'show'])
                ->whereNumber('subscriptionOrder')
                ->name('subscription-orders.show');
        });

    Route::prefix('store')
        ->middleware('auth:sanctum')
        ->name('store.')
        ->group(function (): void {
            Route::get('dashboard', [StoreDashboardController::class, 'show'])
                ->name('dashboard.show');
            Route::apiResource('products', StoreProductController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
            Route::apiResource('product-variants', StoreProductVariantController::class)
                ->only(['index', 'show', 'store', 'update', 'destroy']);
        });

    Route::prefix('{identityType}/auth')
        ->whereIn('identityType', ['admin', 'customer', 'store', 'rider'])
        ->name('auth.')
        ->group(function (): void {
            Route::post('login', [IdentityAuthController::class, 'login'])->name('login');
            Route::middleware('auth:sanctum')->group(function (): void {
                Route::get('me', [IdentityAuthController::class, 'me'])->name('me');
                Route::post('logout', [IdentityAuthController::class, 'logout'])->name('logout');
                Route::get('permissions/{permission}', [IdentityAuthController::class, 'permission'])
                    ->where('permission', '[A-Za-z0-9_.-]+')
                    ->name('permission');
            });
        });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
