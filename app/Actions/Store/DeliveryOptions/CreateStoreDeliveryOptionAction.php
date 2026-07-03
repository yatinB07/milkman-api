<?php

namespace App\Actions\Store\DeliveryOptions;

use App\Data\Store\StoreDeliveryOptionData;
use App\Models\DeliveryOption;
use App\Models\Store;
use App\Repositories\DeliveryOptionRepository;

class CreateStoreDeliveryOptionAction
{
    public function __construct(private readonly DeliveryOptionRepository $deliveryOptions) {}

    public function execute(Store $store, StoreDeliveryOptionData $data): DeliveryOption
    {
        return $this->deliveryOptions->createForStore($store, $data->toArray());
    }
}
