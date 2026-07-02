<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\WalletTransactions\CreateWalletTransactionAction;
use App\Actions\Admin\WalletTransactions\DeleteWalletTransactionAction;
use App\Actions\Admin\WalletTransactions\ListWalletTransactionsAction;
use App\Actions\Admin\WalletTransactions\ShowWalletTransactionAction;
use App\Actions\Admin\WalletTransactions\UpdateWalletTransactionAction;
use App\Exceptions\Auth\MissingPermissionException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ListAdminResourcesRequest;
use App\Http\Requests\Admin\UpdateWalletTransactionRequest;
use App\Http\Requests\Admin\WalletTransactionRequest;
use App\Http\Resources\Admin\WalletTransactionResource;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WalletTransactionController extends Controller
{
    public function index(
        ListAdminResourcesRequest $request,
        IdentityAuthService $auth,
        ListWalletTransactionsAction $transactions,
    ): AnonymousResourceCollection {
        $this->authorizeUserManagement($request, $auth);

        return WalletTransactionResource::collection($transactions->execute($request->toData()));
    }

    public function store(
        WalletTransactionRequest $request,
        IdentityAuthService $auth,
        CreateWalletTransactionAction $create,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.wallet_transaction_created'),
            'data' => new WalletTransactionResource($create->execute($request->toData())),
        ], 201);
    }

    public function show(
        Request $request,
        IdentityAuthService $auth,
        ShowWalletTransactionAction $show,
        int $walletTransaction,
    ): WalletTransactionResource {
        $this->authorizeUserManagement($request, $auth);

        return new WalletTransactionResource($show->execute($walletTransaction));
    }

    public function update(
        UpdateWalletTransactionRequest $request,
        IdentityAuthService $auth,
        UpdateWalletTransactionAction $update,
        int $walletTransaction,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);

        return response()->json([
            'message' => __('catalog.wallet_transaction_updated'),
            'data' => new WalletTransactionResource($update->execute($walletTransaction, $request->toData())),
        ]);
    }

    public function destroy(
        Request $request,
        IdentityAuthService $auth,
        DeleteWalletTransactionAction $delete,
        int $walletTransaction,
    ): JsonResponse {
        $this->authorizeUserManagement($request, $auth);
        $delete->execute($walletTransaction);

        return response()->json([
            'message' => __('catalog.wallet_transaction_deleted'),
        ]);
    }

    private function authorizeUserManagement(Request $request, IdentityAuthService $auth): void
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'admin');

        if (! $identity->can('users.manage')) {
            throw new MissingPermissionException;
        }
    }
}
