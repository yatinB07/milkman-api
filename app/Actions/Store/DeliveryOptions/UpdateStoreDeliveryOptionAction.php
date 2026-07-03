<?php

namespace App\Actions\Store\DeliveryOptions;

use App\Data\Store\StoreDeliveryOptionData;
use App\Models\DeliveryOption;
use App\Models\Store;
use App\Repositories\DeliveryOptionRepository;

class UpdateStoreDeliveryOptionAction
{
    public function __construct(private readonly DeliveryOptionRepository $deliveryOptions) {}

    public function execute(Store $store, int $optionId, StoreDeliveryOptionData $data): DeliveryOption
    {
        return $this->deliveryOptions->update(
            $this->deliveryOptions->findForStore($store, $optionId),
            $data->toArray(),
        );
    }
}
