<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Categories\CreateStoreCategoryAction;
use App\Actions\Store\Categories\DeleteStoreCategoryAction;
use App\Actions\Store\Categories\ListStoreCategoriesAction;
use App\Actions\Store\Categories\ShowStoreCategoryAction;
use App\Actions\Store\Categories\UpdateStoreCategoryAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreCategoryRequest;
use App\Http\Requests\Store\UpdateStoreCategoryRequest;
use App\Http\Resources\Store\StoreCategoryResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreCategoryController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreCategoriesAction $categories,
    ): AnonymousResourceCollection {
        return StoreCategoryResource::collection($categories->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreCategoryRequest $request,
        IdentityAuthService $auth,
        CreateStoreCategoryAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.store_category_created'),
            'data' => new StoreCategoryResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreCategoryAction $show,
        int $category,
    ): StoreCategoryResource {
        return new StoreCategoryResource($show->execute($this->storeIdentity($request, $auth), $category));
    }

    public function update(
        UpdateStoreCategoryRequest $request,
        IdentityAuthService $auth,
        UpdateStoreCategoryAction $update,
        int $category,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.store_category_updated'),
            'data' => new StoreCategoryResource($update->execute($this->storeIdentity($request, $auth), $category, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreCategoryAction $delete,
        int $category,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $category);

        return response()->json([
            'message' => __('catalog.store_category_deleted'),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
