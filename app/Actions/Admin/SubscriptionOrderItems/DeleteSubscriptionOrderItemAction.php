<?php

namespace App\Actions\Admin\SubscriptionOrderItems;

use App\Repositories\SubscriptionOrderItemRepository;

class DeleteSubscriptionOrderItemAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(int $itemId): void
    {
        $this->items->delete($this->items->find($itemId));
    }
}
