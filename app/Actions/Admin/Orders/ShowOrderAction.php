<?php

namespace App\Actions\Admin\Orders;

use App\Models\Order;
use App\Repositories\OrderRepository;

class ShowOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(int $orderId): Order
    {
        return $this->orders->find($orderId);
    }
}
