<?php

namespace App\Actions\Admin\WalletTransactions;

use App\Models\WalletTransaction;
use App\Repositories\WalletTransactionRepository;

class ShowWalletTransactionAction
{
    public function __construct(
        private readonly WalletTransactionRepository $transactions,
    ) {}

    public function execute(int $transactionId): WalletTransaction
    {
        return $this->transactions->find($transactionId);
    }
}
