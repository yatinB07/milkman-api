<?php

namespace App\Actions\Store\DeliveryOptions;

use App\Models\Store;
use App\Repositories\DeliveryOptionRepository;

class DeleteStoreDeliveryOptionAction
{
    public function __construct(private readonly DeliveryOptionRepository $deliveryOptions) {}

    public function execute(Store $store, int $optionId): void
    {
        $this->deliveryOptions->delete($this->deliveryOptions->findForStore($store, $optionId));
    }
}
