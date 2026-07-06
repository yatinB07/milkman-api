<?php

namespace App\Data\Customer;

final readonly class CustomerEmailAvailabilityData
{
    public function __construct(public string $email) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(email: (string) $data['email']);
    }
}
