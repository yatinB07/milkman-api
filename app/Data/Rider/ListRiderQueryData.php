<?php

namespace App\Data\Rider;

class ListRiderQueryData
{
    public const MAX_PER_PAGE = 100;

    public function __construct(
        public readonly ?string $search,
        public readonly int $perPage,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            search: filled($data['search'] ?? null) ? (string) $data['search'] : null,
            perPage: min((int) ($data['per_page'] ?? 15), self::MAX_PER_PAGE),
        );
    }
}
