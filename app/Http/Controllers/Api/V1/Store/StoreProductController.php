<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Products\CreateStoreProductAction;
use App\Actions\Store\Products\DeleteStoreProductAction;
use App\Actions\Store\Products\ListStoreProductsAction;
use App\Actions\Store\Products\ShowStoreProductAction;
use App\Actions\Store\Products\UpdateStoreProductAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreProductRequest;
use App\Http\Requests\Store\UpdateStoreProductRequest;
use App\Http\Resources\Store\StoreProductResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreProductController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreProductsAction $products,
    ): AnonymousResourceCollection {
        return StoreProductResource::collection($products->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreProductRequest $request,
        IdentityAuthService $auth,
        CreateStoreProductAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_created'),
            'data' => new StoreProductResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreProductAction $show,
        int $product,
    ): StoreProductResource {
        return new StoreProductResource($show->execute($this->storeIdentity($request, $auth), $product));
    }

    public function update(
        UpdateStoreProductRequest $request,
        IdentityAuthService $auth,
        UpdateStoreProductAction $update,
        int $product,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.product_updated'),
            'data' => new StoreProductResource($update->execute($this->storeIdentity($request, $auth), $product, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreProductAction $delete,
        int $product,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $product);

        return response()->json([
            'message' => __('catalog.product_deleted'),
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
