<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerOrderRatingData;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\OrderRepository;

class RateCustomerOrderAction
{
    public function __construct(private readonly OrderRepository $orders) {}

    public function execute(Customer $customer, int $orderId, CustomerOrderRatingData $data): Order
    {
        $order = $this->orders->findForCustomer($customer, $orderId);

        return $this->orders->rate($order, $data);
    }
}
