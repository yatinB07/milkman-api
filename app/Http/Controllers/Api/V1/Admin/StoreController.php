<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Stores\CreateStoreAction;
use App\Actions\Admin\Stores\DeleteStoreAction;
use App\Actions\Admin\Stores\ListStoresAction;
use App\Actions\Admin\Stores\ShowStoreAction;
use App\Actions\Admin\Stores\UpdateStoreAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\StoreRequest;
use App\Http\Requests\Admin\UpdateStoreRequest;
use App\Http\Resources\Admin\StoreResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoresAction $stores,
    ): AnonymousResourceCollection {
        $this->authorizeStoreManagement($request, $auth);

        return StoreResource::collection($stores->execute($request->toData()));
    }

    public function store(StoreRequest $request, IdentityAuthService $auth, CreateStoreAction $create): JsonResponse
    {
        $this->authorizeStoreManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_created'),
            'data' => new StoreResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowStoreAction $show, int $store): StoreResource
    {
        $this->authorizeStoreManagement($request, $auth);

        return new StoreResource($show->execute($store));
    }

    public function update(
        UpdateStoreRequest $request,
        IdentityAuthService $auth,
        UpdateStoreAction $update,
        int $store,
    ): JsonResponse {
        $this->authorizeStoreManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_updated'),
            'data' => new StoreResource($update->execute($store, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteStoreAction $delete, int $store): JsonResponse
    {
        $this->authorizeStoreManagement($request, $auth);
        $delete->execute($store);

        return response()->json([
            'message' => __('catalog.store_deleted'),
        ]);
    }

    private function authorizeStoreManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
