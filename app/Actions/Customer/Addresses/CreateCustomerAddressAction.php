<?php

namespace App\Actions\Customer\Addresses;

use App\Data\Customer\CustomerAddressData;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class CreateCustomerAddressAction
{
    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    public function execute(Customer $customer, CustomerAddressData $data): CustomerAddress
    {
        return $this->addresses->create($data->forCustomer($customer->getKey()));
    }
}
