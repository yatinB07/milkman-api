<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\ProductVariants\CreateProductVariantAction;
use App\Actions\Admin\ProductVariants\DeleteProductVariantAction;
use App\Actions\Admin\ProductVariants\ListProductVariantsAction;
use App\Actions\Admin\ProductVariants\ShowProductVariantAction;
use App\Actions\Admin\ProductVariants\UpdateProductVariantAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\ProductVariantRequest;
use App\Http\Requests\Admin\UpdateProductVariantRequest;
use App\Http\Resources\Admin\ProductVariantResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductVariantController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListProductVariantsAction $variants,
    ): AnonymousResourceCollection {
        $this->authorizeProductVariantManagement($request, $auth);

        return ProductVariantResource::collection($variants->execute($request->toData()));
    }

    public function store(
        ProductVariantRequest $request,
        IdentityAuthService $auth,
        CreateProductVariantAction $create,
    ): JsonResponse {
        $this->authorizeProductVariantManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_variant_created'),
            'data' => new ProductVariantResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowProductVariantAction $show,
        int $productVariant,
    ): ProductVariantResource {
        $this->authorizeProductVariantManagement($request, $auth);

        return new ProductVariantResource($show->execute($productVariant));
    }

    public function update(
        UpdateProductVariantRequest $request,
        IdentityAuthService $auth,
        UpdateProductVariantAction $update,
        int $productVariant,
    ): JsonResponse {
        $this->authorizeProductVariantManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.product_variant_updated'),
            'data' => new ProductVariantResource($update->execute($productVariant, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteProductVariantAction $delete,
        int $productVariant,
    ): JsonResponse {
        $this->authorizeProductVariantManagement($request, $auth);
        $delete->execute($productVariant);

        return response()->json([
            'message' => __('catalog.product_variant_deleted'),
        ]);
    }

    private function authorizeProductVariantManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('products.manage')) {
            throw new MissingPermissionException;
        }
    }
}
