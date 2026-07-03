<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Orders\ExtendCustomerSubscriptionScheduleAction;
use App\Actions\Customer\Orders\ListCustomerSubscriptionOrdersAction;
use App\Actions\Customer\Orders\PlaceCustomerSubscriptionOrderAction;
use App\Actions\Customer\Orders\RateCustomerSubscriptionOrderAction;
use App\Actions\Customer\Orders\ShowCustomerSubscriptionOrderAction;
use App\Actions\Customer\Orders\SkipCustomerSubscriptionScheduleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerOrderHistoryRequest;
use App\Http\Requests\Customer\CustomerOrderRatingRequest;
use App\Http\Requests\Customer\CustomerSubscriptionOrderRequest;
use App\Http\Requests\Customer\SubscriptionScheduleChangeRequest;
use App\Http\Resources\Customer\CustomerSubscriptionOrderResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerSubscriptionOrderController extends Controller
{
    public function index(
        CustomerOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListCustomerSubscriptionOrdersAction $orders,
    ): AnonymousResourceCollection {
        return CustomerSubscriptionOrderResource::collection($orders->execute($this->customer($request, $auth), $request->toData()));
    }

    public function store(
        CustomerSubscriptionOrderRequest $request,
        IdentityAuthService $auth,
        PlaceCustomerSubscriptionOrderAction $placeOrder,
    ): JsonResponse {
        $customer = $this->customer($request, $auth);
        $order = $placeOrder->execute($customer, $request->toData());

        return response()->json([
            'message' => __('catalog.customer_subscription_order_placed'),
            'wallet_balance' => $customer->refresh()->getAttribute('wallet_balance'),
            'data' => new CustomerSubscriptionOrderResource($order),
        ], 201);
    }

    public function show(
        Request $request,
        int $subscriptionOrder,
        IdentityAuthService $auth,
        ShowCustomerSubscriptionOrderAction $orderDetail,
    ): CustomerSubscriptionOrderResource {
        return new CustomerSubscriptionOrderResource($orderDetail->execute($this->customer($request, $auth), $subscriptionOrder));
    }

    public function rate(
        CustomerOrderRatingRequest $request,
        int $subscriptionOrder,
        IdentityAuthService $auth,
        RateCustomerSubscriptionOrderAction $rateOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.customer_order_rating_saved'),
            'data' => new CustomerSubscriptionOrderResource($rateOrder->execute($this->customer($request, $auth), $subscriptionOrder, $request->toData())),
        ]);
    }

    public function skip(
        SubscriptionScheduleChangeRequest $request,
        int $subscriptionOrder,
        int $item,
        IdentityAuthService $auth,
        SkipCustomerSubscriptionScheduleAction $skipSchedule,
    ): JsonResponse {
        $customer = $this->customer($request, $auth);
        $order = $skipSchedule->execute($customer, $subscriptionOrder, $item, $request->toData());

        return response()->json([
            'message' => __('catalog.subscription_schedule_skipped'),
            'wallet_balance' => $customer->refresh()->getAttribute('wallet_balance'),
            'data' => new CustomerSubscriptionOrderResource($order),
        ]);
    }

    public function extend(
        SubscriptionScheduleChangeRequest $request,
        int $subscriptionOrder,
        int $item,
        IdentityAuthService $auth,
        ExtendCustomerSubscriptionScheduleAction $extendSchedule,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.subscription_schedule_extended'),
            'data' => new CustomerSubscriptionOrderResource(
                $extendSchedule->execute($this->customer($request, $auth), $subscriptionOrder, $item, $request->toData())
            ),
        ]);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
