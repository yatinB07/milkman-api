<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerOrderHistoryQueryData;
use App\Models\Customer;
use App\Models\SubscriptionOrder;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerSubscriptionOrdersAction
{
    public function __construct(
        private readonly SubscriptionOrderRepository $subscriptionOrders,
    ) {}

    /** @return LengthAwarePaginator<int, SubscriptionOrder> */
    public function execute(Customer $customer, CustomerOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->subscriptionOrders->paginateForCustomer($customer, $query->status, $query->search, $query->perPage);
    }
}
