<?php

namespace App\Actions\Admin\SubscriptionOrders;

use App\Data\Admin\SubscriptionOrderData;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class CreateSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(SubscriptionOrderData $data): SubscriptionOrder
    {
        return $this->orders->create($data->toArray());
    }
}
