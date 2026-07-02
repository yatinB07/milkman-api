<?php

namespace App\Actions\Admin\CustomerAddresses;

use App\Data\Admin\CustomerAddressData;
use App\Models\CustomerAddress;
use App\Repositories\CustomerAddressRepository;

class UpdateCustomerAddressAction
{
    public function __construct(
        private readonly CustomerAddressRepository $addresses,
    ) {}

    public function execute(int $addressId, CustomerAddressData $data): CustomerAddress
    {
        return $this->addresses->update(
            $this->addresses->find($addressId),
            $data->toArray(),
        );
    }
}
