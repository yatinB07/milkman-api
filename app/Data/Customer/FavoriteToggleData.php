<?php

namespace App\Data\Customer;

final readonly class FavoriteToggleData
{
    public function __construct(public int $storeId) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((int) $data['store_id']);
    }
}
