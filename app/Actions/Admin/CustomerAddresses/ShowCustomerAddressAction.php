<?php

namespace App\Actions\Admin\CustomerAddresses;

use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class ShowCustomerAddressAction
{
    public function __construct(
        private readonly CustomerAddressRepository $addresses,
    ) {}

    public function execute(int $addressId): CustomerAddress
    {
        return $this->addresses->find($addressId);
    }
}
