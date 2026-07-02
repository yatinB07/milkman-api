<?php

namespace App\Actions\Customer\Addresses;

use App\Models\Customer;
use App\Repositories\CustomerAddressRepository;

class DeleteCustomerAddressAction
{
    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    public function execute(Customer $customer, int $addressId): void
    {
        $this->addresses->delete($this->addresses->findForCustomer($customer, $addressId));
    }
}
