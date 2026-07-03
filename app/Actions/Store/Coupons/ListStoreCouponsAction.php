<?php

namespace App\Actions\Store\Coupons;

use App\Data\Store\ListStoreQueryData;
use App\Models\Coupon;
use App\Models\Store;
use App\Repositories\CouponRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreCouponsAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    /** @return LengthAwarePaginator<int, Coupon> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->coupons->paginateForStore($store, $query->search, $query->perPage);
    }
}
