<?php

namespace App\Actions\Customer\Stores;

use App\Data\Customer\CustomerStoreSearchQueryData;
use App\Models\Customer;
use App\Models\Store;
use App\Repositories\StoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerStoresAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    /** @return LengthAwarePaginator<int, Store> */
    public function execute(Customer $customer, CustomerStoreSearchQueryData $query): LengthAwarePaginator
    {
        return $this->stores->paginateForCustomer($customer, $query);
    }
}
