<?php

namespace App\Actions\Admin\Coupons;

use App\Data\Admin\ListQueryData;
use App\Repositories\CouponRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCouponsAction
{
    public function __construct(
        private readonly CouponRepository $coupons,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->coupons->paginate($query->search, $query->perPage);
    }
}
