<?php

namespace App\Data\Customer;

final readonly class CustomerMobileAvailabilityData
{
    public function __construct(
        public string $countryCode,
        public string $mobile,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            countryCode: (string) $data['country_code'],
            mobile: (string) $data['mobile'],
        );
    }
}
