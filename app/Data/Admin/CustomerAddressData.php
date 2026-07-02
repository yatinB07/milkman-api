<?php

namespace App\Data\Admin;

final readonly class CustomerAddressData
{
    private const FIELDS = [
        'customer_id',
        'address',
        'landmark',
        'rider_instruction',
        'type',
        'latitude',
        'longitude',
    ];

    /** @param array<string, mixed> $attributes */
    private function __construct(
        private array $attributes,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(self::onlyAllowedFields($data));
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private static function onlyAllowedFields(array $data): array
    {
        return array_filter(
            $data,
            fn (string $field): bool => in_array($field, self::FIELDS, true),
            ARRAY_FILTER_USE_KEY,
        );
    }
}
