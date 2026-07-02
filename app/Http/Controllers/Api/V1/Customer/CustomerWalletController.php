<?php

namespace App\Http\Controllers\Api\V1\Customer;

use App\Actions\Customer\Wallet\ListCustomerWalletTransactionsAction;
use App\Actions\Customer\Wallet\TopUpCustomerWalletAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\ListCustomerResourcesRequest;
use App\Http\Requests\Customer\WalletTopUpRequest;
use App\Http\Resources\Customer\WalletTransactionResource;
use App\Models\Customer;
use App\Services\IdentityAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CustomerWalletController extends Controller
{
    public function index(
        ListCustomerResourcesRequest $request,
        IdentityAuthService $auth,
        ListCustomerWalletTransactionsAction $transactions,
    ): AnonymousResourceCollection {
        $customer = $this->customer($request, $auth);

        return WalletTransactionResource::collection($transactions->execute($customer, $request->toData()))
            ->additional(['wallet_balance' => $customer->getAttribute('wallet_balance')]);
    }

    public function topUp(
        WalletTopUpRequest $request,
        IdentityAuthService $auth,
        TopUpCustomerWalletAction $topUp,
    ): JsonResponse {
        $customer = $this->customer($request, $auth);
        $transaction = $topUp->execute($customer, $request->toData());

        return response()->json([
            'message' => __('catalog.customer_wallet_updated'),
            'wallet_balance' => $customer->refresh()->getAttribute('wallet_balance'),
            'data' => new WalletTransactionResource($transaction),
        ], 201);
    }

    private function customer(Request $request, IdentityAuthService $auth): Customer
    {
        $identity = $request->user();
        $auth->assertTokenMatchesIdentityType($identity, 'customer');

        return $identity;
    }
}
