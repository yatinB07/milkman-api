<?php

namespace App\Actions\Customer\Orders;

use App\Data\Customer\CustomerOrderHistoryQueryData;
use App\Models\Customer;
use App\Models\Order;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerOrdersAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    /** @return LengthAwarePaginator<int, Order> */
    public function execute(Customer $customer, CustomerOrderHistoryQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginateForCustomer($customer, $query->status, $query->search, $query->perPage);
    }
}
