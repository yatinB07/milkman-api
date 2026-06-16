<?php

use App\Http\Controllers\Api\V1\Auth\IdentityAuthController;
use App\Http\Controllers\Api\V1\HealthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::get('health', HealthController::class)->name('health');

    Route::prefix('{identityType}/auth')
        ->whereIn('identityType', ['admin', 'customer', 'store', 'rider'])
        ->name('auth.')
        ->group(function (): void {
            Route::post('login', [IdentityAuthController::class, 'login'])->name('login');
            Route::middleware('auth:sanctum')->group(function (): void {
                Route::get('me', [IdentityAuthController::class, 'me'])->name('me');
                Route::post('logout', [IdentityAuthController::class, 'logout'])->name('logout');
            });
        });
});

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
