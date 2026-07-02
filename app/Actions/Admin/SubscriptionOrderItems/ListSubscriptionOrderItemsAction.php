<?php

namespace App\Actions\Admin\SubscriptionOrderItems;

use App\Data\Admin\ListQueryData;
use App\Repositories\SubscriptionOrderItemRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListSubscriptionOrderItemsAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->items->paginate($query->search, $query->perPage);
    }
}
