<?php

namespace App\Actions\Admin\WalletTransactions;

use App\Data\Admin\ListQueryData;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListWalletTransactionsAction
{
    public function __construct(
        private readonly WalletTransactionRepository $transactions,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->transactions->paginate($query->search, $query->perPage);
    }
}
