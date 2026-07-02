<?php

namespace App\Actions\Admin\SubscriptionOrders;

use App\Repositories\SubscriptionOrderRepository;

class DeleteSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(int $orderId): void
    {
        $this->orders->delete($this->orders->find($orderId));
    }
}
