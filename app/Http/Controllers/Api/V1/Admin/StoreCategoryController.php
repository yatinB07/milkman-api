<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\StoreCategories\CreateStoreCategoryAction;
use App\Actions\Admin\StoreCategories\DeleteStoreCategoryAction;
use App\Actions\Admin\StoreCategories\ListStoreCategoriesAction;
use App\Actions\Admin\StoreCategories\ShowStoreCategoryAction;
use App\Actions\Admin\StoreCategories\UpdateStoreCategoryAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateStoreCategoryRequest;
use App\Http\Resources\Admin\StoreCategoryResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreCategoryController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreCategoriesAction $storeCategories,
    ): AnonymousResourceCollection {
        $this->authorizeStoreCategoryManagement($request, $auth);

        return StoreCategoryResource::collection($storeCategories->execute($request->toData()));
    }

    public function store(
        StoreCategoryRequest $request,
        IdentityAuthService $auth,
        CreateStoreCategoryAction $create,
    ): JsonResponse {
        $this->authorizeStoreCategoryManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_category_created'),
            'data' => new StoreCategoryResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreCategoryAction $show,
        int $storeCategory,
    ): StoreCategoryResource {
        $this->authorizeStoreCategoryManagement($request, $auth);

        return new StoreCategoryResource($show->execute($storeCategory));
    }

    public function update(
        UpdateStoreCategoryRequest $request,
        IdentityAuthService $auth,
        UpdateStoreCategoryAction $update,
        int $storeCategory,
    ): JsonResponse {
        $this->authorizeStoreCategoryManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.store_category_updated'),
            'data' => new StoreCategoryResource($update->execute($storeCategory, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreCategoryAction $delete,
        int $storeCategory,
    ): JsonResponse {
        $this->authorizeStoreCategoryManagement($request, $auth);
        $delete->execute($storeCategory);

        return response()->json([
            'message' => __('catalog.store_category_deleted'),
        ]);
    }

    private function authorizeStoreCategoryManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }
    }
}
