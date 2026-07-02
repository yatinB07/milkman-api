<?php

namespace App\Actions\Admin\SubscriptionOrderItems;

use App\Data\Admin\SubscriptionOrderItemData;
use App\Models\SubscriptionOrderItem;
use App\Repositories\SubscriptionOrderItemRepository;

class CreateSubscriptionOrderItemAction
{
    public function __construct(private readonly SubscriptionOrderItemRepository $items) {}

    public function execute(SubscriptionOrderItemData $data): SubscriptionOrderItem
    {
        return $this->items->create($data->toArray());
    }
}
