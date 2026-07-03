<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\Orders\DecideStoreOrderAction;
use App\Actions\Store\Orders\ListStoreOrdersAction;
use App\Actions\Store\Orders\ShowStoreOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\StoreOrderDecisionRequest;
use App\Http\Requests\Store\StoreOrderHistoryRequest;
use App\Http\Resources\Store\StoreOrderResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
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

    public function decide(
        StoreOrderDecisionRequest $request,
        IdentityAuthService $auth,
        DecideStoreOrderAction $decide,
        int $order,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.order_decision_updated'),
            'data' => new StoreOrderResource($decide->execute($this->storeIdentity($request, $auth, 'orders.update-status'), $order, $request->toData())),
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
