<?php

namespace App\Actions\Customer\Coupons;

use App\Data\Customer\CouponCheckData;
use App\Models\Coupon;
use App\Repositories\CouponRepository;

class CheckCustomerCouponAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(CouponCheckData $data): Coupon
    {
        return $this->coupons->findActive($data->couponId);
    }
}
