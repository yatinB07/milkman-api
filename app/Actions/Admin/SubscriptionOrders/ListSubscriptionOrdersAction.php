<?php

namespace App\Actions\Admin\SubscriptionOrders;

use App\Data\Admin\ListQueryData;
use App\Repositories\SubscriptionOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSubscriptionOrdersAction
{
    public function __construct(private readonly SubscriptionOrderRepository $orders) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginate($query->search, $query->perPage);
    }
}
