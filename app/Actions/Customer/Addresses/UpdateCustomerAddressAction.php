<?php

namespace App\Actions\Customer\Addresses;

use App\Data\Customer\CustomerAddressData;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class UpdateCustomerAddressAction
{
    public function __construct(private readonly CustomerAddressRepository $addresses) {}

    public function execute(Customer $customer, int $addressId, CustomerAddressData $data): CustomerAddress
    {
        return $this->addresses->update($this->addresses->findForCustomer($customer, $addressId), $data->toArray());
    }
}
