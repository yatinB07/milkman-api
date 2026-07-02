<?php

namespace App\Actions\Admin\Customers;

use App\Models\Customer;
use App\Repositories\CustomerRepository;

class ShowCustomerAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(int $customerId): Customer
    {
        return $this->customers->find($customerId);
    }
}
