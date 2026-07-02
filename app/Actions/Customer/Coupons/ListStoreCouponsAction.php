<?php

namespace App\Actions\Customer\Coupons;

use App\Data\Customer\ListCustomerQueryData;
use App\Repositories\CouponRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreCouponsAction
{
    public function __construct(private readonly CouponRepository $coupons) {}

    public function execute(int $storeId, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->coupons->paginateActiveForStore($storeId, $query->search, $query->perPage);
    }
}
