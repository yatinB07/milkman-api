<?php

namespace App\Data\Rider;

final readonly class RiderOrderHistoryQueryData
{
    public const DEFAULT_PER_PAGE = 15;

    public const MAX_PER_PAGE = 100;

    public function __construct(
        public string $status,
        public ?string $search,
        public int $perPage = self::DEFAULT_PER_PAGE,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $search = $data['search'] ?? null;

        return new self(
            status: (string) ($data['status'] ?? 'current'),
            search: is_string($search) && $search !== '' ? $search : null,
            perPage: (int) ($data['per_page'] ?? self::DEFAULT_PER_PAGE),
        );
    }
}
