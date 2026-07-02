<?php

namespace App\Actions\Admin\DeliveryOptions;

use App\Data\Admin\DeliveryOptionData;
use App\Models\DeliveryOption;
use App\Repositories\DeliveryOptionRepository;

class CreateDeliveryOptionAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    public function execute(DeliveryOptionData $data): DeliveryOption
    {
        return $this->deliveryOptions->create($data->toArray());
    }
}
