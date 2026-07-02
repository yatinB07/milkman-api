<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Products\CreateProductAction;
use App\Actions\Admin\Products\DeleteProductAction;
use App\Actions\Admin\Products\ListProductsAction;
use App\Actions\Admin\Products\ShowProductAction;
use App\Actions\Admin\Products\UpdateProductAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\ProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Http\Resources\Admin\ProductResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListProductsAction $products,
    ): AnonymousResourceCollection {
        $this->authorizeProductManagement($request, $auth);

        return ProductResource::collection($products->execute($request->toData()));
    }

    public function store(ProductRequest $request, IdentityAuthService $auth, CreateProductAction $create): JsonResponse
    {
        $this->authorizeProductManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_created'),
            'data' => new ProductResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(Request $request, IdentityAuthService $auth, ShowProductAction $show, int $product): ProductResource
    {
        $this->authorizeProductManagement($request, $auth);

        return new ProductResource($show->execute($product));
    }

    public function update(
        UpdateProductRequest $request,
        IdentityAuthService $auth,
        UpdateProductAction $update,
        int $product,
    ): JsonResponse {
        $this->authorizeProductManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_updated'),
            'data' => new ProductResource($update->execute($product, $request->toData())),
        ]);
    }

    public function destroy(Request $request, IdentityAuthService $auth, DeleteProductAction $delete, int $product): JsonResponse
    {
        $this->authorizeProductManagement($request, $auth);
        $delete->execute($product);

        return response()->json([
            'message' => __('catalog.product_deleted'),
        ]);
    }

    private function authorizeProductManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }
    }
}
