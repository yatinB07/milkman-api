<?php

namespace App\Actions\Admin\OrderItems;

use App\Data\Admin\ListQueryData;
use App\Repositories\OrderItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListOrderItemsAction
{
    public function __construct(private readonly OrderItemRepository $items) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->items->paginate($query->search, $query->perPage);
    }
}
