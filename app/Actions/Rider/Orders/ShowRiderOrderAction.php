<?php

namespace App\Actions\Rider\Orders;

use App\Models\Order;
use App\Models\Rider;
use App\Repositories\OrderRepository;

class ShowRiderOrderAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function execute(Rider $rider, int $orderId): Order
    {
        return $this->orders->findForRider($rider, $orderId);
    }
}
