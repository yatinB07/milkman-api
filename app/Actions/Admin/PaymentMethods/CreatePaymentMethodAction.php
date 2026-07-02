<?php

namespace App\Actions\Admin\PaymentMethods;

use App\Data\Admin\PaymentMethodData;
use App\Models\PaymentMethod;
use App\Repositories\PaymentMethodRepository;

class CreatePaymentMethodAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethods,
    ) {}

    public function execute(PaymentMethodData $data): PaymentMethod
    {
        return $this->paymentMethods->create($data->toArray());
    }
}
