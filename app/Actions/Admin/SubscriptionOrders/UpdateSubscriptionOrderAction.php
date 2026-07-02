<?php

namespace App\Actions\Admin\SubscriptionOrders;

use App\Data\Admin\SubscriptionOrderData;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class UpdateSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(int $orderId, SubscriptionOrderData $data): SubscriptionOrder
    {
        return $this->orders->update($this->orders->find($orderId), $data->toArray());
    }
}
