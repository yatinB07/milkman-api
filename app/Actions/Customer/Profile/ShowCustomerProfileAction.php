<?php

namespace App\Actions\Customer\Profile;

use App\Models\Customer;
use App\Repositories\SettingRepository;

class ShowCustomerProfileAction
{
    public function __construct(private readonly SettingRepository $settings) {}

    /** @return array{customer: Customer, referral: array{signup_credit: string, referral_credit: string}} */
    public function execute(Customer $customer): array
    {
        $setting = $this->settings->current();

        return [
            'customer' => $customer,
            'referral' => [
                'signup_credit' => $setting?->getAttribute('signup_credit') ?? '0.00',
                'referral_credit' => $setting?->getAttribute('referral_credit') ?? '0.00',
            ],
        ];
    }
}
