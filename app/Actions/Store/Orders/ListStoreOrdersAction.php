<?php

namespace App\Actions\Store\Orders;

use App\Data\Store\StoreOrderHistoryQueryData;
use App\Models\Order;
use App\Models\Store;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreOrdersAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    /** @return LengthAwarePaginator<int, Order> */
    public function execute(Store $store, StoreOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginateForStore($store, $query->status, $query->search, $query->perPage);
    }
}
