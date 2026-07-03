<?php

namespace App\Actions\Customer\Orders;

use App\Models\Customer;
use App\Models\Order;
use App\Repositories\OrderRepository;

class ShowCustomerOrderAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(Customer $customer, int $orderId): Order
    {
        return $this->orders->findForCustomer($customer, $orderId);
    }
}
