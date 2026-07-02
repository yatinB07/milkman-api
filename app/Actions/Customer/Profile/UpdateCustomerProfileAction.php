<?php

namespace App\Actions\Customer\Profile;

use App\Data\Customer\CustomerProfileData;
use App\Models\Customer;
use App\Repositories\CustomerRepository;

class UpdateCustomerProfileAction
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function execute(Customer $customer, CustomerProfileData $data): Customer
    {
        return $this->customers->update($customer, $data->toArray());
    }
}
