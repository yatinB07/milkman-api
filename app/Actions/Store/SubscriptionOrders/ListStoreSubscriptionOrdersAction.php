<?php

namespace App\Actions\Store\SubscriptionOrders;

use App\Data\Store\StoreOrderHistoryQueryData;
use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreSubscriptionOrdersAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    /** @return LengthAwarePaginator<int, SubscriptionOrder> */
    public function execute(Store $store, StoreOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginateForStore($store, $query->status, $query->search, $query->perPage);
    }
}
