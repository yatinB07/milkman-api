<?php

namespace App\Actions\Admin\Orders;

use App\Repositories\OrderRepository;

class DeleteOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(int $orderId): void
    {
        $this->orders->delete(
            $this->orders->find($orderId),
        );
    }
}
