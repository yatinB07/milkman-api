<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\SubscriptionOrders\ListStoreSubscriptionOrdersAction;
use App\Actions\Store\SubscriptionOrders\ShowStoreSubscriptionOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreOrderHistoryRequest;
use App\Http\Resources\Store\StoreSubscriptionOrderResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
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

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('orders.view')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
