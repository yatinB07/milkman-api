<?php

namespace App\Actions\Admin\Orders;

use App\Data\Admin\ListQueryData;
use App\Repositories\OrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListOrdersAction
{
    public function __construct(
        private readonly OrderRepository $orders,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->orders->paginate($query->search, $query->perPage);
    }
}
