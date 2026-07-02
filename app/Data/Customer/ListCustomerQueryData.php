<?php

namespace App\Data\Customer;

final readonly class ListCustomerQueryData
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

    public function __construct(
        public ?string $search,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $search = $data['search'] ?? null;
        $perPage = $data['per_page'] ?? self::DEFAULT_PER_PAGE;

        return new self(
            search: is_string($search) && $search !== '' ? $search : null,
            perPage: (int) $perPage,
        );
    }
}
