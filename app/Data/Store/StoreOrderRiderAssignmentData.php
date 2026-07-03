<?php

namespace App\Data\Store;

final readonly class StoreOrderRiderAssignmentData
{
    public function __construct(public int $riderId) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(riderId: (int) $data['rider_id']);
    }
}
