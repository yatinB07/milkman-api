<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Favorites\ListCustomerFavoritesAction;
use App\Actions\Customer\Favorites\ToggleCustomerFavoriteAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\FavoriteToggleRequest;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\FavoriteResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerFavoriteController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerFavoritesAction $favorites,
    ): AnonymousResourceCollection {
        return FavoriteResource::collection(
            $favorites->execute($this->customer($request, $auth), $request->toData()),
        );
    }

    public function toggle(
        FavoriteToggleRequest $request,
        IdentityAuthService $auth,
        ToggleCustomerFavoriteAction $toggle,
    ): JsonResponse {
        $result = $toggle->execute($this->customer($request, $auth), $request->toData());

        return response()->json([
            'message' => $result['is_favorite']
                ? __('catalog.customer_favorite_saved')
                : __('catalog.customer_favorite_removed'),
            'is_favorite' => $result['is_favorite'],
            'data' => new FavoriteResource($result['favorite']),
        ]);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
