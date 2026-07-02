<?php

namespace App\Actions\Admin\Coupons;

use App\Data\Admin\CouponData;
use App\Models\Coupon;
use App\Repositories\CouponRepository;

class CreateCouponAction
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {}

    public function execute(CouponData $data): Coupon
    {
        return $this->coupons->create($data->toArray());
    }
}
