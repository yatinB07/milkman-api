<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Orders\ListStoreOrdersAction;
use App\Actions\Store\Orders\ShowStoreOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreOrderHistoryRequest;
use App\Http\Resources\Store\StoreOrderResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StoreOrderController extends Controller
{
    public function index(
        StoreOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListStoreOrdersAction $orders,
    ): AnonymousResourceCollection {
        return StoreOrderResource::collection($orders->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStoreOrderAction $show,
        int $order,
    ): StoreOrderResource {
        return new StoreOrderResource($show->execute($this->storeIdentity($request, $auth), $order));
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
