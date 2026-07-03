<?php

namespace App\Actions\Store\Coupons;

use App\Models\Coupon;
use App\Models\Store;
use App\Repositories\CouponRepository;

class ShowStoreCouponAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(Store $store, int $couponId): Coupon
    {
        return $this->coupons->findForStore($store, $couponId);
    }
}
