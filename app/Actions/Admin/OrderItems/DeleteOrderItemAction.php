<?php

namespace App\Actions\Admin\OrderItems;

use App\Repositories\OrderItemRepository;

class DeleteOrderItemAction
{
    public function __construct(private readonly OrderItemRepository $items) {}

    public function execute(int $itemId): void
    {
        $this->items->delete($this->items->find($itemId));
    }
}
