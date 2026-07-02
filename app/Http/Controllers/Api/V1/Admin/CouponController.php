<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\Coupons\CreateCouponAction;
use App\Actions\Admin\Coupons\DeleteCouponAction;
use App\Actions\Admin\Coupons\ListCouponsAction;
use App\Actions\Admin\Coupons\ShowCouponAction;
use App\Actions\Admin\Coupons\UpdateCouponAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCouponRequest;
use App\Http\Resources\Admin\CouponResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CouponController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCouponsAction $coupons,
    ): AnonymousResourceCollection {
        $this->authorizeCouponManagement($request, $auth);

        return CouponResource::collection($coupons->execute($request->toData()));
    }

    public function store(
        CouponRequest $request,
        IdentityAuthService $auth,
        CreateCouponAction $create,
    ): JsonResponse {
        $this->authorizeCouponManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.coupon_created'),
            'data' => new CouponResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCouponAction $show,
        int $coupon,
    ): CouponResource {
        $this->authorizeCouponManagement($request, $auth);

        return new CouponResource($show->execute($coupon));
    }

    public function update(
        UpdateCouponRequest $request,
        IdentityAuthService $auth,
        UpdateCouponAction $update,
        int $coupon,
    ): JsonResponse {
        $this->authorizeCouponManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.coupon_updated'),
            'data' => new CouponResource($update->execute($coupon, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteCouponAction $delete,
        int $coupon,
    ): JsonResponse {
        $this->authorizeCouponManagement($request, $auth);
        $delete->execute($coupon);

        return response()->json([
            'message' => __('catalog.coupon_deleted'),
        ]);
    }

    private function authorizeCouponManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('stores.manage')) {
            throw new MissingPermissionException;
        }
    }
}
