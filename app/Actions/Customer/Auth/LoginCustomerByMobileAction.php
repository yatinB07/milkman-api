<?php

namespace App\Actions\Customer\Auth;

use App\Data\Customer\CustomerMobileLoginData;
use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Repositories\CustomerRepository;
use Illuminate\Support\Facades\Hash;

class LoginCustomerByMobileAction
{
    public function __construct(private readonly CustomerRepository $customers) {}

    /** @return array{identity: object, token: string} */
    public function execute(CustomerMobileLoginData $data): array
    {
        $customer = $this->customers->findLoginCandidateByCountryCodeAndIdentifier(
            $data->countryCode,
            $data->mobile,
        );

        if (! $customer || ! Hash::check($data->password, $customer->getAttribute('password'))) {
            throw new InvalidCredentialsException;
        }

        if (! $customer->getAttribute('is_active')) {
            throw new InactiveAccountException;
        }

        return [
            'identity' => $customer,
            'token' => $customer->createToken('customer-api-token')->plainTextToken,
        ];
    }
}
