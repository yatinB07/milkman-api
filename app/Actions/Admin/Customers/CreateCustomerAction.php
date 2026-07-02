<?php

namespace App\Actions\Admin\Customers;

use App\Data\Admin\CustomerData;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

class CreateCustomerAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(CustomerData $data): Customer
    {
        return $this->customers->create($data->toArray());
    }
}
