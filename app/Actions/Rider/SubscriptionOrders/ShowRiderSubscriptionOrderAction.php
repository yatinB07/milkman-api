<?php

namespace App\Actions\Rider\SubscriptionOrders;

use App\Models\Rider;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class ShowRiderSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(Rider $rider, int $orderId): SubscriptionOrder
    {
        return $this->orders->findForRider($rider, $orderId);
    }
}
