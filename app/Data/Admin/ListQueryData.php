<?php

namespace App\Data\Admin;

final readonly class ListQueryData
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

    public function __construct(
        public ?string $search,
        public int $perPage = self::DEFAULT_PER_PAGE,
        public ?bool $isActive = null,
        public ?bool $isOutOfStock = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $search = $data['search'] ?? null;
        $perPage = $data['per_page'] ?? self::DEFAULT_PER_PAGE;

        return new self(
            search: is_string($search) && $search !== '' ? $search : null,
            perPage: (int) $perPage,
            isActive: self::optionalBoolean($data['is_active'] ?? null),
            isOutOfStock: self::optionalBoolean($data['is_out_of_stock'] ?? null),
        );
    }

    private static function optionalBoolean(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
