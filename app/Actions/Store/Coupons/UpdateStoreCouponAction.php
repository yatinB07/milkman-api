<?php

namespace App\Actions\Store\Coupons;

use App\Data\Store\StoreCouponData;
use App\Models\Coupon;
use App\Models\Store;
use App\Repositories\CouponRepository;

class UpdateStoreCouponAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(Store $store, int $couponId, StoreCouponData $data): Coupon
    {
        return $this->coupons->update(
            $this->coupons->findForStore($store, $couponId),
            $data->toArray(),
        );
    }
}
