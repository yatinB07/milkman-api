<?php

namespace App\Data\Customer;

final readonly class CouponCheckData
{
    public function __construct(public int $couponId) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self((int) $data['coupon_id']);
    }
}
