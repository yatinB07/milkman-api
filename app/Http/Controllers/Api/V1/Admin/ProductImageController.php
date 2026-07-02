<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\ProductImages\CreateProductImageAction;
use App\Actions\Admin\ProductImages\DeleteProductImageAction;
use App\Actions\Admin\ProductImages\ListProductImagesAction;
use App\Actions\Admin\ProductImages\ShowProductImageAction;
use App\Actions\Admin\ProductImages\UpdateProductImageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\ProductImageRequest;
use App\Http\Requests\Admin\UpdateProductImageRequest;
use App\Http\Resources\Admin\ProductImageResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductImageController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListProductImagesAction $images,
    ): AnonymousResourceCollection {
        $this->authorizeProductImageManagement($request, $auth);

        return ProductImageResource::collection($images->execute($request->toData()));
    }

    public function store(
        ProductImageRequest $request,
        IdentityAuthService $auth,
        CreateProductImageAction $create,
    ): JsonResponse {
        $this->authorizeProductImageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_image_created'),
            'data' => new ProductImageResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowProductImageAction $show,
        int $productImage,
    ): ProductImageResource {
        $this->authorizeProductImageManagement($request, $auth);

        return new ProductImageResource($show->execute($productImage));
    }

    public function update(
        UpdateProductImageRequest $request,
        IdentityAuthService $auth,
        UpdateProductImageAction $update,
        int $productImage,
    ): JsonResponse {
        $this->authorizeProductImageManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_image_updated'),
            'data' => new ProductImageResource($update->execute($productImage, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteProductImageAction $delete,
        int $productImage,
    ): JsonResponse {
        $this->authorizeProductImageManagement($request, $auth);
        $delete->execute($productImage);

        return response()->json([
            'message' => __('catalog.product_image_deleted'),
        ]);
    }

    private function authorizeProductImageManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }
    }
}
