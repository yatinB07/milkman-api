<?php

namespace App\Actions\Admin\SubscriptionOrderItems;

use App\Models\SubscriptionOrderItem;
use App\Repositories\SubscriptionOrderItemRepository;

class ShowSubscriptionOrderItemAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(int $itemId): SubscriptionOrderItem
    {
        return $this->items->find($itemId);
    }
}
