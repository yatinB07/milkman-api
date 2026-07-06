<?php

namespace App\Actions\Customer\Auth;

use App\Data\Customer\CustomerEmailAvailabilityData;
use App\Repositories\CustomerRepository;

class CheckCustomerEmailAvailabilityAction
{
    public function __construct(private readonly CustomerRepository $customers) {}

    /** @return array{field: string, available: bool, message: string} */
    public function execute(CustomerEmailAvailabilityData $data): array
    {
        $available = ! $this->customers->existsByEmail($data->email);

        return [
            'field' => 'email',
            'available' => $available,
            'message' => $available
                ? __('auth.customer_email_available')
                : __('auth.customer_email_unavailable'),
        ];
    }
}
