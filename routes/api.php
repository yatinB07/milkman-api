<?php

use App\Http\Controllers\Api\V1\Admin\BannerController;
use App\Http\Controllers\Api\V1\Admin\CategoryController;
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
                ->only(['index', 'store', 'update', 'destroy']);
            Route::apiResource('categories', CategoryController::class)
                ->only(['index', 'store', 'update', 'destroy']);
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
