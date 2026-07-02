<?php

namespace App\Actions\Admin\SubscriptionOrders;

use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class ShowSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(int $orderId): SubscriptionOrder
    {
        return $this->orders->find($orderId);
    }
}
