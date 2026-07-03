<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Orders\ListCustomerOrdersAction;
use App\Actions\Customer\Orders\PlaceCustomerOrderAction;
use App\Actions\Customer\Orders\ShowCustomerOrderAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\CustomerOrderHistoryRequest;
use App\Http\Requests\Customer\CustomerOrderRequest;
use App\Http\Resources\Customer\CustomerOrderResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerOrderController extends Controller
{
    public function index(
        CustomerOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListCustomerOrdersAction $orders,
    ): AnonymousResourceCollection {
        return CustomerOrderResource::collection($orders->execute($this->customer($request, $auth), $request->toData()));
    }

    public function store(
        CustomerOrderRequest $request,
        IdentityAuthService $auth,
        PlaceCustomerOrderAction $placeOrder,
    ): JsonResponse {
        $customer = $this->customer($request, $auth);
        $order = $placeOrder->execute($customer, $request->toData());

        return response()->json([
            'message' => __('catalog.customer_order_placed'),
            'wallet_balance' => $customer->refresh()->getAttribute('wallet_balance'),
            'data' => new CustomerOrderResource($order),
        ], 201);
    }

    public function show(
        Request $request,
        int $order,
        IdentityAuthService $auth,
        ShowCustomerOrderAction $orderDetail,
    ): CustomerOrderResource {
        return new CustomerOrderResource($orderDetail->execute($this->customer($request, $auth), $order));
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
