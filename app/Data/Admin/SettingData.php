<?php

namespace App\Data\Admin;

final readonly class SettingData
{
    private const FIELDS = [
        'web_name',
        'web_logo_path',
        'timezone',
        'currency',
        'primary_store_id',
        'customer_onesignal_key',
        'customer_onesignal_hash',
        'delivery_onesignal_key',
        'delivery_onesignal_hash',
        'store_onesignal_key',
        'store_onesignal_hash',
        'signup_credit',
        'referral_credit',
        'show_dark_mode',
        'google_maps_key',
        'sms_type',
        'message_auth_key',
        'otp_template_id',
        'twilio_account_sid',
        'twilio_auth_token',
        'twilio_number',
        'otp_auth_token',
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
