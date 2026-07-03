<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Riders\CreateStoreRiderAction;
use App\Actions\Store\Riders\DeleteStoreRiderAction;
use App\Actions\Store\Riders\ListStoreRidersAction;
use App\Actions\Store\Riders\ShowStoreRiderAction;
use App\Actions\Store\Riders\UpdateStoreRiderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreRiderRequest;
use App\Http\Requests\Store\UpdateStoreRiderRequest;
use App\Http\Resources\Store\StoreRiderResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreRiderController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreRidersAction $riders,
    ): AnonymousResourceCollection {
        return StoreRiderResource::collection($riders->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreRiderRequest $request,
        IdentityAuthService $auth,
        CreateStoreRiderAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.rider_created'),
            'data' => new StoreRiderResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreRiderAction $show,
        int $rider,
    ): StoreRiderResource {
        return new StoreRiderResource($show->execute($this->storeIdentity($request, $auth), $rider));
    }

    public function update(
        UpdateStoreRiderRequest $request,
        IdentityAuthService $auth,
        UpdateStoreRiderAction $update,
        int $rider,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.rider_updated'),
            'data' => new StoreRiderResource($update->execute($this->storeIdentity($request, $auth), $rider, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreRiderAction $delete,
        int $rider,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $rider);

        return response()->json([
            'message' => __('catalog.rider_deleted'),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('riders.manage')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
