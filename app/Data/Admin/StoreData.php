<?php

namespace App\Data\Admin;

final readonly class StoreData
{
    private const FIELDS = [
        'title',
        'zone_id',
        'image_path',
        'cover_image_path',
        'rating',
        'slogan',
        'slogan_title',
        'language_code',
        'category_reference',
        'email',
        'password',
        'country_code',
        'mobile',
        'full_address',
        'pincode',
        'landmark',
        'short_description',
        'content_description',
        'latitude',
        'longitude',
        'store_charge',
        'delivery_charge',
        'minimum_order_amount',
        'commission_percent',
        'opens_at',
        'closes_at',
        'is_pickup_enabled',
        'is_active',
        'registration_status',
        'charge_type',
        'unit_kilometers',
        'unit_price',
        'additional_price',
        'bank_name',
        'ifsc_code',
        'receipt_name',
        'account_number',
        'paypal_id',
        'upi_id',
        'cancel_policy',
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
