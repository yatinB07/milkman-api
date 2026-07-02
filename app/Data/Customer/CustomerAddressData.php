<?php

namespace App\Data\Customer;

final readonly class CustomerAddressData
{
    private const FIELDS = [
        'address',
        'landmark',
        'rider_instruction',
        'type',
        'latitude',
        'longitude',
    ];

    /** @param array<string, mixed> $attributes */
    private function __construct(private array $attributes) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(array_filter(
            $data,
            fn (string $field): bool => in_array($field, self::FIELDS, true),
            ARRAY_FILTER_USE_KEY,
        ));
    }

    /** @return array<string, mixed> */
    public function forCustomer(int $customerId): array
    {
        return [
            ...$this->attributes,
            'customer_id' => $customerId,
        ];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->attributes;
    }
}
