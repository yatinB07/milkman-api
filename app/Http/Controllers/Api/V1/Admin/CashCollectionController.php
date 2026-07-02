<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\CashCollections\CreateCashCollectionAction;
use App\Actions\Admin\CashCollections\DeleteCashCollectionAction;
use App\Actions\Admin\CashCollections\ListCashCollectionsAction;
use App\Actions\Admin\CashCollections\ShowCashCollectionAction;
use App\Actions\Admin\CashCollections\UpdateCashCollectionAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CashCollectionRequest;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateCashCollectionRequest;
use App\Http\Resources\Admin\CashCollectionResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CashCollectionController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListCashCollectionsAction $collections,
    ): AnonymousResourceCollection {
        $this->authorizePayoutApproval($request, $auth);

        return CashCollectionResource::collection($collections->execute($request->toData()));
    }

    public function store(
        CashCollectionRequest $request,
        IdentityAuthService $auth,
        CreateCashCollectionAction $create,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);

        return response()->json([
            'message' => __('catalog.cash_collection_created'),
            'data' => new CashCollectionResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowCashCollectionAction $show,
        int $cashCollection,
    ): CashCollectionResource {
        $this->authorizePayoutApproval($request, $auth);

        return new CashCollectionResource($show->execute($cashCollection));
    }

    public function update(
        UpdateCashCollectionRequest $request,
        IdentityAuthService $auth,
        UpdateCashCollectionAction $update,
        int $cashCollection,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);

        return response()->json([
            'message' => __('catalog.cash_collection_updated'),
            'data' => new CashCollectionResource($update->execute($cashCollection, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteCashCollectionAction $delete,
        int $cashCollection,
    ): JsonResponse {
        $this->authorizePayoutApproval($request, $auth);
        $delete->execute($cashCollection);

        return response()->json([
            'message' => __('catalog.cash_collection_deleted'),
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
