<?php

namespace App\Data\Admin;

final readonly class SubscriptionOrderData
{
    private const FIELDS = [
        'store_id',
        'customer_id',
        'ordered_at',
        'payment_method_id',
        'address',
        'landmark',
        'delivery_charge',
        'coupon_id',
        'coupon_amount',
        'total',
        'subtotal',
        'transaction_id',
        'admin_note',
        'admin_status',
        'rider_id',
        'wallet_amount',
        'customer_name',
        'customer_mobile',
        'status',
        'rejection_comment',
        'time_slot',
        'order_type',
        'is_rated',
        'reviewed_at',
        'total_rating',
        'rating_text',
        'commission_percent',
        'store_charge',
        'internal_status',
        'signature_path',
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
