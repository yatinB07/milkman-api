<?php

namespace App\Actions\Admin\DeliveryOptions;

use App\Data\Admin\DeliveryOptionData;
use App\Models\DeliveryOption;
use App\Repositories\DeliveryOptionRepository;

class UpdateDeliveryOptionAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    public function execute(int $deliveryOptionId, DeliveryOptionData $data): DeliveryOption
    {
        return $this->deliveryOptions->update(
            $this->deliveryOptions->find($deliveryOptionId),
            $data->toArray(),
        );
    }
}
