<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Orders\PlaceCustomerSubscriptionOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerSubscriptionOrderRequest;
use App\Http\Resources\Customer\CustomerSubscriptionOrderResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerSubscriptionOrderController extends Controller
{
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

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
