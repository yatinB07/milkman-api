<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\PayoutRequests\CreatePayoutRequestAction;
use App\Actions\Admin\PayoutRequests\DeletePayoutRequestAction;
use App\Actions\Admin\PayoutRequests\ListPayoutRequestsAction;
use App\Actions\Admin\PayoutRequests\ShowPayoutRequestAction;
use App\Actions\Admin\PayoutRequests\UpdatePayoutRequestAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\PayoutRequestRequest;
use App\Http\Requests\Admin\UpdatePayoutRequestRequest;
use App\Http\Resources\Admin\PayoutRequestResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PayoutRequestController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListPayoutRequestsAction $payouts,
    ): AnonymousResourceCollection {
        $this->authorizePayoutApproval($request, $auth);

        return PayoutRequestResource::collection($payouts->execute($request->toData()));
    }

    public function store(
        PayoutRequestRequest $request,
        IdentityAuthService $auth,
        CreatePayoutRequestAction $create,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);

        return response()->json([
            'message' => __('catalog.payout_request_created'),
            'data' => new PayoutRequestResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowPayoutRequestAction $show,
        int $payoutRequest,
    ): PayoutRequestResource {
        $this->authorizePayoutApproval($request, $auth);

        return new PayoutRequestResource($show->execute($payoutRequest));
    }

    public function update(
        UpdatePayoutRequestRequest $request,
        IdentityAuthService $auth,
        UpdatePayoutRequestAction $update,
        int $payoutRequest,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);

        return response()->json([
            'message' => __('catalog.payout_request_updated'),
            'data' => new PayoutRequestResource($update->execute($payoutRequest, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeletePayoutRequestAction $delete,
        int $payoutRequest,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);
        $delete->execute($payoutRequest);

        return response()->json([
            'message' => __('catalog.payout_request_deleted'),
        ]);
    }

    private function authorizePayoutApproval(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('payouts.approve')) {
            throw new MissingPermissionException;
        }
    }
}
