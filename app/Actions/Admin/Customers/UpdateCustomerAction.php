<?php

namespace App\Actions\Admin\Customers;

use App\Data\Admin\CustomerData;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

class UpdateCustomerAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(int $customerId, CustomerData $data): Customer
    {
        return $this->customers->update(
            $this->customers->find($customerId),
            $data->toArray(),
        );
    }
}
