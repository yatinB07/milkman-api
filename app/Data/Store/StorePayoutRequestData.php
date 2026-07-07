<?php

namespace App\Data\Store;

final readonly class StorePayoutRequestData
{
    private const FIELDS = [
        'amount',
        'request_type',
        'account_number',
        'bank_name',
        'account_name',
        'ifsc_code',
        'upi_id',
        'paypal_id',
    ];

    /** @param array<string, mixed> $attributes */
    private function __construct(private array $attributes) {}

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

    public function amount(): float
    {
        return (float) $this->attributes['amount'];
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
