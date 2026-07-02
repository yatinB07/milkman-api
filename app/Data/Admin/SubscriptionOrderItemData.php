<?php

namespace App\Data\Admin;

final readonly class SubscriptionOrderItemData
{
    private const FIELDS = [
        'subscription_order_id',
        'quantity',
        'product_title',
        'discount',
        'image_path',
        'price',
        'variant_title',
        'starts_at',
        'total_deliveries',
        'total_dates',
        'completed_dates',
        'selected_days',
        'time_slot',
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
    public function toArray(): array
    {
        return $this->attributes;
    }
}
