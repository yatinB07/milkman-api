<?php

namespace App\Actions\Customer\Auth;

use App\Data\Customer\CustomerPasswordResetData;
use App\Repositories\CustomerRepository;

class ResetCustomerPasswordAction
{
    public function __construct(private readonly CustomerRepository $customers) {}

    public function execute(CustomerPasswordResetData $data): void
    {
        $customer = $this->customers->findByCountryCodeAndMobile($data->countryCode, $data->mobile);

        $this->customers->updatePassword($customer, $data->password);
    }
}
