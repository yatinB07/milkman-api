<?php

namespace App\Actions\Admin\Coupons;

use App\Models\Coupon;
use App\Repositories\CouponRepository;

class ShowCouponAction
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {}

    public function execute(int $couponId): Coupon
    {
        return $this->coupons->find($couponId);
    }
}
