<?php

namespace App\Actions\Admin\Orders;

use App\Data\Admin\OrderData;
use App\Models\Order;
use App\Repositories\OrderRepository;

class CreateOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(OrderData $data): Order
    {
        return $this->orders->create($data->toArray());
    }
}
