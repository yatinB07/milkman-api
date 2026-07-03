<?php

namespace App\Actions\Store\DeliveryOptions;

use App\Models\DeliveryOption;
use App\Models\Store;
use App\Repositories\DeliveryOptionRepository;

class ShowStoreDeliveryOptionAction
{
    public function __construct(private readonly DeliveryOptionRepository $deliveryOptions) {}

    public function execute(Store $store, int $optionId): DeliveryOption
    {
        return $this->deliveryOptions->findForStore($store, $optionId);
    }
}
