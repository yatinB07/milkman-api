<?php

namespace App\Actions\Store\Coupons;

use App\Models\Store;
use App\Repositories\CouponRepository;

class DeleteStoreCouponAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(Store $store, int $couponId): void
    {
        $this->coupons->delete($this->coupons->findForStore($store, $couponId));
    }
}
