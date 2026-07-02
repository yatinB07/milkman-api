<?php

namespace App\Actions\Admin\OrderItems;

use App\Data\Admin\OrderItemData;
use App\Models\OrderItem;
use App\Repositories\OrderItemRepository;

class UpdateOrderItemAction
{
    public function __construct(private readonly OrderItemRepository $items) {}

    public function execute(int $itemId, OrderItemData $data): OrderItem
    {
        return $this->items->update($this->items->find($itemId), $data->toArray());
    }
}
