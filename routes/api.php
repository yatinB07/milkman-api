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
use App\Http\Controllers\Api\V1\Admin\TimeSlotController;
use App\Http\Controllers\Api\V1\Admin\WalletTransactionController;
use App\Http\Controllers\Api\V1\Admin\ZoneController;
use App\Http\Controllers\Api\V1\Auth\IdentityAuthController;
use App\Http\Controllers\Api\V1\Catalog\PublicCatalogController;
use App\Http\Controllers\Api\V1\HealthController;
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
