<?php

namespace App\Actions\Admin\WalletTransactions;

use App\Data\Admin\WalletTransactionData;
use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;

class UpdateWalletTransactionAction
{
    public function __construct(
        private readonly WalletTransactionRepository $transactions,
    ) {}

    public function execute(int $transactionId, WalletTransactionData $data): WalletTransaction
    {
        return $this->transactions->update(
            $this->transactions->find($transactionId),
            $data->toArray(),
        );
    }
}
