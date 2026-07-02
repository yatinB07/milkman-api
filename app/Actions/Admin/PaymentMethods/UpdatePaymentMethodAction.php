<?php

namespace App\Actions\Admin\PaymentMethods;

use App\Data\Admin\PaymentMethodData;
use App\Models\PaymentMethod;
use App\Repositories\PaymentMethodRepository;

class UpdatePaymentMethodAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethods,
    ) {}

    public function execute(int $paymentMethodId, PaymentMethodData $data): PaymentMethod
    {
        return $this->paymentMethods->update(
            $this->paymentMethods->find($paymentMethodId),
            $data->toArray(),
        );
    }
}
