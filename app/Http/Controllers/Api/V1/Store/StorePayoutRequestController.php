<?php

namespace App\Http\Controllers\Api\V1\Store;

use App\Actions\Store\PayoutRequests\CreateStorePayoutRequestAction;
use App\Actions\Store\PayoutRequests\ListStorePayoutRequestsAction;
use App\Actions\Store\PayoutRequests\ShowStorePayoutRequestAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Store\ListStoreResourcesRequest;
use App\Http\Requests\Store\StorePayoutRequestRequest;
use App\Http\Resources\Store\StorePayoutRequestResource;
use App\Models\Store;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StorePayoutRequestController extends Controller
{
    public function index(
        ListStoreResourcesRequest $request,
        IdentityAuthService $auth,
        ListStorePayoutRequestsAction $payouts,
    ): AnonymousResourceCollection {
        return StorePayoutRequestResource::collection($payouts->execute($this->storeIdentity($request, $auth), $request->toData()));
    }

    public function store(
        StorePayoutRequestRequest $request,
        IdentityAuthService $auth,
        CreateStorePayoutRequestAction $create,
    ): JsonResponse {
        return response()->json([
            'message' => __('catalog.payout_request_created'),
            'data' => new StorePayoutRequestResource($create->execute($this->storeIdentity($request, $auth), $request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowStorePayoutRequestAction $show,
        int $payoutRequest,
    ): StorePayoutRequestResource {
        return new StorePayoutRequestResource($show->execute($this->storeIdentity($request, $auth), $payoutRequest));
    }

    private function storeIdentity(Request $request, IdentityAuthService $auth): Store
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'store');

        if (! $identity->can('payouts.request')) {
            throw new MissingPermissionException;
        }

        return $identity;
    }
}
