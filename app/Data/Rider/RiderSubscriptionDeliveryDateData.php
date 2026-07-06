<?php

namespace App\Data\Rider;

final readonly class RiderSubscriptionDeliveryDateData
{
    public function __construct(public string $selectedDate) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(selectedDate: (string) $data['selected_date']);
    }
}
