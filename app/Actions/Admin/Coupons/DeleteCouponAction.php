<?php

namespace App\Actions\Admin\Coupons;

use App\Repositories\CouponRepository;

class DeleteCouponAction
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {}

    public function execute(int $couponId): void
    {
        $this->coupons->delete(
            $this->coupons->find($couponId),
        );
    }
}
