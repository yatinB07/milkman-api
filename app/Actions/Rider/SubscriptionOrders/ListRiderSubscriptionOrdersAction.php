<?php

namespace App\Actions\Rider\SubscriptionOrders;

use App\Data\Rider\RiderOrderHistoryQueryData;
use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRiderSubscriptionOrdersAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    /** @return LengthAwarePaginator<int, SubscriptionOrder> */
    public function execute(Rider $rider, RiderOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginateForRider($rider, $query->status, $query->search, $query->perPage);
    }
}
