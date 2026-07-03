<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\DeliveryOptions\CreateStoreDeliveryOptionAction;
use App\Actions\Store\DeliveryOptions\DeleteStoreDeliveryOptionAction;
use App\Actions\Store\DeliveryOptions\ListStoreDeliveryOptionsAction;
use App\Actions\Store\DeliveryOptions\ShowStoreDeliveryOptionAction;
use App\Actions\Store\DeliveryOptions\UpdateStoreDeliveryOptionAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreDeliveryOptionRequest;
use App\Http\Requests\Store\UpdateStoreDeliveryOptionRequest;
use App\Http\Resources\Store\StoreDeliveryOptionResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreDeliveryOptionController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreDeliveryOptionsAction $deliveryOptions,
    ): AnonymousResourceCollection {
        return StoreDeliveryOptionResource::collection($deliveryOptions->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreDeliveryOptionRequest $request,
        IdentityAuthService $auth,
        CreateStoreDeliveryOptionAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.delivery_option_created'),
            'data' => new StoreDeliveryOptionResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreDeliveryOptionAction $show,
        int $deliveryOption,
    ): StoreDeliveryOptionResource {
        return new StoreDeliveryOptionResource($show->execute($this->storeIdentity($request, $auth), $deliveryOption));
    }

    public function update(
        UpdateStoreDeliveryOptionRequest $request,
        IdentityAuthService $auth,
        UpdateStoreDeliveryOptionAction $update,
        int $deliveryOption,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.delivery_option_updated'),
            'data' => new StoreDeliveryOptionResource($update->execute($this->storeIdentity($request, $auth), $deliveryOption, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreDeliveryOptionAction $delete,
        int $deliveryOption,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $deliveryOption);

        return response()->json([
            'message' => __('catalog.delivery_option_deleted'),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('stores.update')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
