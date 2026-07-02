<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Coupons\CheckCustomerCouponAction;
use App\Actions\Customer\Coupons\ListStoreCouponsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CouponCheckRequest;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Resources\Customer\CouponResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerCouponController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListStoreCouponsAction $coupons,
        int $store,
    ): AnonymousResourceCollection {
        $this->customer($request, $auth);

        return CouponResource::collection($coupons->execute($store, $request->toData()));
    }

    public function check(
        CouponCheckRequest $request,
        IdentityAuthService $auth,
        CheckCustomerCouponAction $check,
    ): JsonResponse {
        $this->customer($request, $auth);

        return response()->json([
            'message' => __('catalog.customer_coupon_applied'),
            'data' => new CouponResource($check->execute($request->toData())),
        ]);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
