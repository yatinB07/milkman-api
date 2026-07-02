<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Categories\CreateCategoryAction;
use App\Actions\Admin\Categories\DeleteCategoryAction;
use App\Actions\Admin\Categories\ListCategoriesAction;
use App\Actions\Admin\Categories\ShowCategoryAction;
use App\Actions\Admin\Categories\UpdateCategoryAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Http\Resources\Admin\CategoryResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CategoryController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCategoriesAction $categories,
    ): AnonymousResourceCollection {
        $this->authorizeCategoryManagement($request, $auth);

        return CategoryResource::collection($categories->execute($request->toData()));
    }

    public function store(CategoryRequest $request, IdentityAuthService $auth, CreateCategoryAction $create): JsonResponse
    {
        $this->authorizeCategoryManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.category_created'),
            'data' => new CategoryResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowCategoryAction $show, int $category): CategoryResource
    {
        $this->authorizeCategoryManagement($request, $auth);

        return new CategoryResource($show->execute($category));
    }

    public function update(
        UpdateCategoryRequest $request,
        IdentityAuthService $auth,
        UpdateCategoryAction $update,
        int $category,
    ): JsonResponse {
        $this->authorizeCategoryManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.category_updated'),
            'data' => new CategoryResource($update->execute($category, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteCategoryAction $delete, int $category): JsonResponse
    {
        $this->authorizeCategoryManagement($request, $auth);
        $delete->execute($category);

        return response()->json([
            'message' => __('catalog.category_deleted'),
        ]);
    }

    private function authorizeCategoryManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }
    }
}
