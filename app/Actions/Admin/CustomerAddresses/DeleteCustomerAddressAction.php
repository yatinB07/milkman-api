<?php

namespace App\Actions\Admin\CustomerAddresses;

use App\Repositories\CustomerAddressRepository;

class DeleteCustomerAddressAction
{
    public function __construct(
        private readonly CustomerAddressRepository $addresses,
    ) {}

    public function execute(int $addressId): void
    {
        $this->addresses->delete(
            $this->addresses->find($addressId),
        );
    }
}
