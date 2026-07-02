<?php

namespace App\Actions\Admin\WalletTransactions;

use App\Repositories\WalletTransactionRepository;

class DeleteWalletTransactionAction
{
    public function __construct(
        private readonly WalletTransactionRepository $transactions,
    ) {}

    public function execute(int $transactionId): void
    {
        $this->transactions->delete(
            $this->transactions->find($transactionId),
        );
    }
}
