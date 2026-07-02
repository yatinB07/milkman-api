<?php

namespace App\Actions\Admin\Coupons;

use App\Data\Admin\CouponData;
use App\Models\Coupon;
use App\Repositories\CouponRepository;

class UpdateCouponAction
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {}

    public function execute(int $couponId, CouponData $data): Coupon
    {
        return $this->coupons->update(
            $this->coupons->find($couponId),
            $data->toArray(),
        );
    }
}
