<?php

namespace App\Actions\Store\SubscriptionOrders;

use App\Models\Store;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class ShowStoreSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(Store $store, int $orderId): SubscriptionOrder
    {
        return $this->orders->findForStore($store, $orderId);
    }
}
