<?php

namespace App\Actions\Customer\Auth;

use App\Data\Customer\CustomerMobileAvailabilityData;
use App\Repositories\CustomerRepository;

class CheckCustomerMobileAvailabilityAction
{
    public function __construct(private readonly CustomerRepository $customers) {}

    /** @return array{field: string, available: bool, message: string} */
    public function execute(CustomerMobileAvailabilityData $data): array
    {
        $available = ! $this->customers->existsByCountryCodeAndMobile($data->countryCode, $data->mobile);

        return [
            'field' => 'mobile',
            'available' => $available,
            'message' => $available
                ? __('auth.customer_mobile_available')
                : __('auth.customer_mobile_unavailable'),
        ];
    }
}
