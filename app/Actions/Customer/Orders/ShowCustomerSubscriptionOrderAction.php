<?php

namespace App\Actions\Customer\Orders;

use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;

class ShowCustomerSubscriptionOrderAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $subscriptionOrders,
    ) {}

    public function execute(Customer $customer, int $orderId): SubscriptionOrder
    {
        return $this->subscriptionOrders->findForCustomer($customer, $orderId);
    }
}
