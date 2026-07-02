<?php

namespace App\Actions\Customer\Addresses;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Customer;
use App\Repositories\CustomerAddressRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerAddressesAction
{
    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    public function execute(Customer $customer, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->addresses->paginateForCustomer($customer, $query->search, $query->perPage);
    }
}
