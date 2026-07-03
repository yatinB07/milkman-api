<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Coupons\CreateStoreCouponAction;
use App\Actions\Store\Coupons\DeleteStoreCouponAction;
use App\Actions\Store\Coupons\ListStoreCouponsAction;
use App\Actions\Store\Coupons\ShowStoreCouponAction;
use App\Actions\Store\Coupons\UpdateStoreCouponAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StoreCouponRequest;
use App\Http\Requests\Store\UpdateStoreCouponRequest;
use App\Http\Resources\Store\StoreCouponResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreCouponController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreCouponsAction $coupons,
    ): AnonymousResourceCollection {
        return StoreCouponResource::collection($coupons->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StoreCouponRequest $request,
        IdentityAuthService $auth,
        CreateStoreCouponAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.coupon_created'),
            'data' => new StoreCouponResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreCouponAction $show,
        int $coupon,
    ): StoreCouponResource {
        return new StoreCouponResource($show->execute($this->storeIdentity($request, $auth), $coupon));
    }

    public function update(
        UpdateStoreCouponRequest $request,
        IdentityAuthService $auth,
        UpdateStoreCouponAction $update,
        int $coupon,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.coupon_updated'),
            'data' => new StoreCouponResource($update->execute($this->storeIdentity($request, $auth), $coupon, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteStoreCouponAction $delete,
        int $coupon,
    ): JsonResponse {
        $delete->execute($this->storeIdentity($request, $auth), $coupon);

        return response()->json([
            'message' => __('catalog.coupon_deleted'),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('stores.update')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
