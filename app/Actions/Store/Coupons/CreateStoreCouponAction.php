<?php

namespace App\Actions\Store\Coupons;

use App\Data\Store\StoreCouponData;
use App\Models\Coupon;
use App\Models\Store;
use App\Repositories\CouponRepository;

class CreateStoreCouponAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(Store $store, StoreCouponData $data): Coupon
    {
        return $this->coupons->createForStore($store, $data->toArray());
    }
}
