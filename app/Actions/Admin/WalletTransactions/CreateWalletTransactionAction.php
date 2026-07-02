<?php

namespace App\Actions\Admin\WalletTransactions;

use App\Data\Admin\WalletTransactionData;
use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;

class CreateWalletTransactionAction
{
    public function __construct(
        private readonly WalletTransactionRepository $transactions,
    ) {}

    public function execute(WalletTransactionData $data): WalletTransaction
    {
        return $this->transactions->create($data->toArray());
    }
}
