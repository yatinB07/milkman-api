<?php

namespace App\Actions\Admin\DeliveryOptions;

use App\Models\DeliveryOption;
use App\Repositories\DeliveryOptionRepository;

class ShowDeliveryOptionAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    public function execute(int $deliveryOptionId): DeliveryOption
    {
        return $this->deliveryOptions->find($deliveryOptionId);
    }
}
