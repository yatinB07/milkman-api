<?php

namespace App\Actions\Customer\Addresses;

use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class ShowCustomerAddressAction
{
    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    public function execute(Customer $customer, int $addressId): CustomerAddress
    {
        return $this->addresses->findForCustomer($customer, $addressId);
    }
}
