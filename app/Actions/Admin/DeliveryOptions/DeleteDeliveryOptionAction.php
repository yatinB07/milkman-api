<?php

namespace App\Actions\Admin\DeliveryOptions;

use App\Repositories\DeliveryOptionRepository;

class DeleteDeliveryOptionAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    public function execute(int $deliveryOptionId): void
    {
        $this->deliveryOptions->delete(
            $this->deliveryOptions->find($deliveryOptionId),
        );
    }
}
