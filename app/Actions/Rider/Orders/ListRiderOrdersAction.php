<?php

namespace App\Actions\Rider\Orders;

use App\Data\Rider\RiderOrderHistoryQueryData;
use App\Models\Order;
use App\Models\Rider;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRiderOrdersAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    /** @return LengthAwarePaginator<int, Order> */
    public function execute(Rider $rider, RiderOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginateForRider($rider, $query->status, $query->search, $query->perPage);
    }
}
