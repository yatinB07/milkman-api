<?php

namespace App\Actions\Admin\Customers;

use App\Repositories\CustomerRepository;

class DeleteCustomerAction
{
    public function __construct(
        private readonly CustomerRepository $customers,
    ) {}

    public function execute(int $customerId): void
    {
        $this->customers->delete(
            $this->customers->find($customerId),
        );
    }
}
