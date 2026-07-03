<?php

namespace App\Actions\Store\Orders;

use App\Models\Order;
use App\Models\Store;
use App\Repositories\OrderRepository;

class ShowStoreOrderAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function execute(Store $store, int $orderId): Order
    {
        return $this->orders->findForStore($store, $orderId);
    }
}
