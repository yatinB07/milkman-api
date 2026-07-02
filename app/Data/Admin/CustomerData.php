<?php

namespace App\Data\Admin;

final readonly class CustomerData
{
    private const FIELDS = [
        'name',
        'profile_image_path',
        'email',
        'country_code',
        'mobile',
        'password',
        'registered_at',
        'referral_code',
        'parent_referral_code',
        'wallet_balance',
        'is_active',
        'email_verified_at',
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
