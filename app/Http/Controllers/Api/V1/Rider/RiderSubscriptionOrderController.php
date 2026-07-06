<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\SubscriptionOrders\CompleteRiderSubscriptionOrderAction;
use App\Actions\Rider\SubscriptionOrders\DecideRiderSubscriptionOrderAction;
use App\Actions\Rider\SubscriptionOrders\ListRiderSubscriptionOrdersAction;
use App\Actions\Rider\SubscriptionOrders\ShowRiderSubscriptionOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\RiderOrderCompletionRequest;
use App\Http\Requests\Rider\RiderOrderDecisionRequest;
use App\Http\Requests\Rider\RiderOrderHistoryRequest;
use App\Http\Resources\Rider\RiderSubscriptionOrderResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderSubscriptionOrderController extends Controller
{
    public function index(
        RiderOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListRiderSubscriptionOrdersAction $orders,
    ): AnonymousResourceCollection {
        return RiderSubscriptionOrderResource::collection($orders->execute($this->riderIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderSubscriptionOrderAction $show,
        int $subscriptionOrder,
    ): RiderSubscriptionOrderResource {
        return new RiderSubscriptionOrderResource($show->execute($this->riderIdentity($request, $auth), $subscriptionOrder));
    }

    public function decide(
        RiderOrderDecisionRequest $request,
        IdentityAuthService $auth,
        DecideRiderSubscriptionOrderAction $decide,
        int $subscriptionOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.subscription_order_decision_updated'),
            'data' => new RiderSubscriptionOrderResource($decide->execute($this->riderIdentity($request, $auth, 'orders.update-status'), $subscriptionOrder, $request->toData())),
        ]);
    }

    public function complete(
        RiderOrderCompletionRequest $request,
        IdentityAuthService $auth,
        CompleteRiderSubscriptionOrderAction $complete,
        int $subscriptionOrder,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.subscription_order_completed'),
            'data' => new RiderSubscriptionOrderResource($complete->execute($this->riderIdentity($request, $auth, 'orders.update-status'), $subscriptionOrder, $request->toData())),
        ]);
    }

    private function riderIdentity(Request $request, IdentityAuthService $auth, string $permission = 'orders.view'): Rider
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'rider');

        if (! $identity->can($permission)) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
