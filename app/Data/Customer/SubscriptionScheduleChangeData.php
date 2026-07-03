<?php

namespace App\Data\Customer;

final readonly class SubscriptionScheduleChangeData
{
    /** @param array<int, string> $dates */
    public function __construct(public array $dates) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(dates: array_values($data['dates']));
    }
}
