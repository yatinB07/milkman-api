<?php

namespace App\Data\Customer;

final readonly class CustomerHomeQueryData
{
    public const DEFAULT_PER_PAGE = 5;

    public const MAX_PER_PAGE = 20;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            perPage: (int) ($data['per_page'] ?? self::DEFAULT_PER_PAGE),
        );
    }
}
