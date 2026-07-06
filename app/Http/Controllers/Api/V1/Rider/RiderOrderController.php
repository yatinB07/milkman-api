<?php

namespace App\Http\Controllers\Api\V1\Rider;

use App\Actions\Rider\Orders\ListRiderOrdersAction;
use App\Actions\Rider\Orders\ShowRiderOrderAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Rider\RiderOrderHistoryRequest;
use App\Http\Resources\Rider\RiderOrderResource;
use App\Models\Rider;
use App\Services\IdentityAuthService;
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
