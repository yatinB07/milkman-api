<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\ProductVariants\CreateStoreProductVariantAction;
use App\Actions\Store\ProductVariants\DeleteStoreProductVariantAction;
use App\Actions\Store\ProductVariants\ListStoreProductVariantsAction;
use App\Actions\Store\ProductVariants\ShowStoreProductVariantAction;
use App\Actions\Store\ProductVariants\UpdateStoreProductVariantAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreProductVariantRequest;
use App\Http\Requests\Store\UpdateStoreProductVariantRequest;
use App\Http\Resources\Store\StoreProductVariantResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreProductVariantController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreProductVariantsAction $variants,
    ): AnonymousResourceCollection {
        return StoreProductVariantResource::collection($variants->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreProductVariantRequest $request,
        IdentityAuthService $auth,
        CreateStoreProductVariantAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_variant_created'),
            'data' => new StoreProductVariantResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreProductVariantAction $show,
        int $productVariant,
    ): StoreProductVariantResource {
        return new StoreProductVariantResource($show->execute($this->storeIdentity($request, $auth), $productVariant));
    }

    public function update(
        UpdateStoreProductVariantRequest $request,
        IdentityAuthService $auth,
        UpdateStoreProductVariantAction $update,
        int $productVariant,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_variant_updated'),
            'data' => new StoreProductVariantResource($update->execute($this->storeIdentity($request, $auth), $productVariant, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreProductVariantAction $delete,
        int $productVariant,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $productVariant);

        return response()->json([
            'message' => __('catalog.product_variant_deleted'),
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
