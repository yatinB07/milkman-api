<?php

namespace App\Data\Customer;

final readonly class CustomerCartDataQueryData
{
    public function __construct(
        public float $latitude,
        public float $longitude,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
        );
    }
}
