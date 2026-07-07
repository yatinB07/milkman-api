<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\SubscriptionOrders\AssignStoreSubscriptionOrderRiderAction;
use App\Actions\Store\SubscriptionOrders\CompleteStoreSelfPickupSubscriptionOrderAction;
use App\Actions\Store\SubscriptionOrders\DecideStoreSubscriptionOrderAction;
use App\Actions\Store\SubscriptionOrders\ListStoreSubscriptionOrdersAction;
use App\Actions\Store\SubscriptionOrders\ShowStoreSubscriptionOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreOrderDecisionRequest;
use App\Http\Requests\Store\StoreOrderHistoryRequest;
use App\Http\Requests\Store\StoreOrderRiderAssignmentRequest;
use App\Http\Resources\Store\StoreSubscriptionOrderResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreSubscriptionOrderController extends Controller
{
    public function index(
        StoreOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListStoreSubscriptionOrdersAction $orders,
    ): AnonymousResourceCollection {
        return StoreSubscriptionOrderResource::collection($orders->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreSubscriptionOrderAction $show,
        int $subscriptionOrder,
    ): StoreSubscriptionOrderResource {
        return new StoreSubscriptionOrderResource($show->execute($this->storeIdentity($request, $auth), $subscriptionOrder));
    }

    public function decide(
        StoreOrderDecisionRequest $request,
        IdentityAuthService $auth,
        DecideStoreSubscriptionOrderAction $decide,
        int $subscriptionOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.subscription_order_decision_updated'),
            'data' => new StoreSubscriptionOrderResource($decide->execute($this->storeIdentity($request, $auth, 'orders.update-status'), $subscriptionOrder, $request->toData())),
        ]);
    }

    public function assignRider(
        StoreOrderRiderAssignmentRequest $request,
        IdentityAuthService $auth,
        AssignStoreSubscriptionOrderRiderAction $assign,
        int $subscriptionOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.rider_assigned'),
            'data' => new StoreSubscriptionOrderResource($assign->execute($this->storeIdentity($request, $auth, 'orders.assign'), $subscriptionOrder, $request->toData())),
        ]);
    }

    public function complete(
        Request $request,
        IdentityAuthService $auth,
        CompleteStoreSelfPickupSubscriptionOrderAction $complete,
        int $subscriptionOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.subscription_order_completed'),
            'data' => new StoreSubscriptionOrderResource($complete->execute($this->storeIdentity($request, $auth, 'orders.update-status'), $subscriptionOrder)),
        ]);
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth, string $permission = 'orders.view'): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can($permission)) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
