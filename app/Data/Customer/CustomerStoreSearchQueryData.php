<?php

namespace App\Data\Customer;

final readonly class CustomerStoreSearchQueryData
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

    public function __construct(
        public float $latitude,
        public float $longitude,
        public ?string $search,
        public ?int $categoryId,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $search = $data['search'] ?? null;

        return new self(
            latitude: (float) $data['latitude'],
            longitude: (float) $data['longitude'],
            search: is_string($search) && $search !== '' ? $search : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            perPage: (int) ($data['per_page'] ?? self::DEFAULT_PER_PAGE),
        );
    }
}
