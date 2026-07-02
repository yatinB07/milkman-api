<?php

namespace App\Actions\Admin\SubscriptionOrderItems;

use App\Data\Admin\SubscriptionOrderItemData;
use App\Models\SubscriptionOrderItem;
use App\Repositories\SubscriptionOrderItemRepository;

class UpdateSubscriptionOrderItemAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(int $itemId, SubscriptionOrderItemData $data): SubscriptionOrderItem
    {
        return $this->items->update($this->items->find($itemId), $data->toArray());
    }
}
