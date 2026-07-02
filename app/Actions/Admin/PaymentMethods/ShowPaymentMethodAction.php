<?php

namespace App\Actions\Admin\PaymentMethods;

use App\Models\PaymentMethod;
use App\Repositories\PaymentMethodRepository;

class ShowPaymentMethodAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethods,
    ) {}

    public function execute(int $paymentMethodId): PaymentMethod
    {
        return $this->paymentMethods->find($paymentMethodId);
    }
}
