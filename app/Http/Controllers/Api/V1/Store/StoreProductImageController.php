<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\ProductImages\CreateStoreProductImageAction;
use App\Actions\Store\ProductImages\DeleteStoreProductImageAction;
use App\Actions\Store\ProductImages\ListStoreProductImagesAction;
use App\Actions\Store\ProductImages\ShowStoreProductImageAction;
use App\Actions\Store\ProductImages\UpdateStoreProductImageAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreProductImageRequest;
use App\Http\Requests\Store\UpdateStoreProductImageRequest;
use App\Http\Resources\Store\StoreProductImageResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreProductImageController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreProductImagesAction $images,
    ): AnonymousResourceCollection {
        return StoreProductImageResource::collection($images->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreProductImageRequest $request,
        IdentityAuthService $auth,
        CreateStoreProductImageAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_image_created'),
            'data' => new StoreProductImageResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreProductImageAction $show,
        int $productImage,
    ): StoreProductImageResource {
        return new StoreProductImageResource($show->execute($this->storeIdentity($request, $auth), $productImage));
    }

    public function update(
        UpdateStoreProductImageRequest $request,
        IdentityAuthService $auth,
        UpdateStoreProductImageAction $update,
        int $productImage,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_image_updated'),
            'data' => new StoreProductImageResource($update->execute($this->storeIdentity($request, $auth), $productImage, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreProductImageAction $delete,
        int $productImage,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $productImage);

        return response()->json([
            'message' => __('catalog.product_image_deleted'),
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
