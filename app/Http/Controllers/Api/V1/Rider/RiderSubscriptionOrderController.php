<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\SubscriptionOrders\ListRiderSubscriptionOrdersAction;
use App\Actions\Rider\SubscriptionOrders\ShowRiderSubscriptionOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\RiderOrderHistoryRequest;
use App\Http\Resources\Rider\RiderSubscriptionOrderResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
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

    private function riderIdentity(Request $request, IdentityAuthService $auth): Rider
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'rider');

        if (! $identity->can('orders.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
