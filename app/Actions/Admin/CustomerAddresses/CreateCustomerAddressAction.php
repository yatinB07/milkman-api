<?php

namespace App\Actions\Admin\CustomerAddresses;

use App\Data\Admin\CustomerAddressData;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class CreateCustomerAddressAction
{
    public function __construct(
        private readonly CustomerAddressRepository $addresses,
    ) {}

    public function execute(CustomerAddressData $data): CustomerAddress
    {
        return $this->addresses->create($data->toArray());
    }
}
