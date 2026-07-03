<?php

namespace App\Data\Customer;

final readonly class CustomerOrderRatingData
{
    public function __construct(
        public int $totalRating,
        public string $ratingText,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            totalRating: (int) $data['total_rating'],
            ratingText: (string) $data['rating_text'],
        );
    }
}
