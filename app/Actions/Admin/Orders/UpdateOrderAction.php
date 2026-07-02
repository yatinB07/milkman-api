<?php

namespace App\Actions\Admin\Orders;

use App\Data\Admin\OrderData;
use App\Models\Order;
use App\Repositories\OrderRepository;

class UpdateOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(int $orderId, OrderData $data): Order
    {
        return $this->orders->update(
            $this->orders->find($orderId),
            $data->toArray(),
        );
    }
}
