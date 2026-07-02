<?php

namespace App\Actions\Admin\OrderItems;

use App\Models\OrderItem;
use App\Repositories\OrderItemRepository;

class ShowOrderItemAction
{
    public function __construct(private readonly OrderItemRepository $items) {}

    public function execute(int $itemId): OrderItem
    {
        return $this->items->find($itemId);
    }
}
