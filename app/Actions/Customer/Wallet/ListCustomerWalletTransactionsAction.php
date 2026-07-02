<?php

namespace App\Actions\Customer\Wallet;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Customer;
use App\Repositories\WalletTransactionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerWalletTransactionsAction
{
    public function __construct(private readonly WalletTransactionRepository $transactions) {}

    public function execute(Customer $customer, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->transactions->paginateForCustomer($customer, $query->search, $query->perPage);
    }
}
