<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerOrderRatingData;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class RateCustomerSubscriptionOrderAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(Customer $customer, int $orderId, CustomerOrderRatingData $data): SubscriptionOrder
    {
        $order = $this->orders->findForCustomer($customer, $orderId);

        return $this->orders->rate($order, $data);
    }
}
