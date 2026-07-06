<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Orders\CompleteRiderOrderAction;
use App\Actions\Rider\Orders\DecideRiderOrderAction;
use App\Actions\Rider\Orders\ListRiderOrdersAction;
use App\Actions\Rider\Orders\ShowRiderOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\RiderOrderCompletionRequest;
use App\Http\Requests\Rider\RiderOrderDecisionRequest;
use App\Http\Requests\Rider\RiderOrderHistoryRequest;
use App\Http\Resources\Rider\RiderOrderResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RiderOrderController extends Controller
{
    public function index(
        RiderOrderHistoryRequest $request,
        IdentityAuthService $auth,
        ListRiderOrdersAction $orders,
    ): AnonymousResourceCollection {
        return RiderOrderResource::collection($orders->execute($this->riderIdentity($request, $auth), $request->toData()));
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowRiderOrderAction $show,
        int $order,
    ): RiderOrderResource {
        return new RiderOrderResource($show->execute($this->riderIdentity($request, $auth), $order));
    }

    public function decide(
        RiderOrderDecisionRequest $request,
        IdentityAuthService $auth,
        DecideRiderOrderAction $decide,
        int $order,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.order_decision_updated'),
            'data' => new RiderOrderResource($decide->execute($this->riderIdentity($request, $auth, 'orders.update-status'), $order, $request->toData())),
        ]);
    }

    public function complete(
        RiderOrderCompletionRequest $request,
        IdentityAuthService $auth,
        CompleteRiderOrderAction $complete,
        int $order,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.order_completed'),
            'data' => new RiderOrderResource($complete->execute($this->riderIdentity($request, $auth, 'orders.update-status'), $order, $request->toData())),
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
