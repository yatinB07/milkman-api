<?php

namespace App\Actions\Customer\Wallet;

use App\Data\Customer\WalletTopUpData;
use App\Models\Customer;
use App\Models\WalletTransaction;
use App\Services\WalletService;

class TopUpCustomerWalletAction
{
    public function __construct(private readonly WalletService $wallets) {}

    public function execute(Customer $customer, WalletTopUpData $data): WalletTransaction
    {
        return $this->wallets->credit($customer, $data->amount, __('catalog.wallet_balance_added'));
    }
}
