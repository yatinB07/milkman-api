<?php

namespace App\Actions\Admin\OrderItems;

use App\Data\Admin\OrderItemData;
use App\Models\OrderItem;
use App\Repositories\OrderItemRepository;

class CreateOrderItemAction
{
    public function __construct(private readonly OrderItemRepository $items) {}

    public function execute(OrderItemData $data): OrderItem
    {
        return $this->items->create($data->toArray());
    }
}
