<?php

namespace App\Actions\Admin\PaymentMethods;

use App\Repositories\PaymentMethodRepository;

class DeletePaymentMethodAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethods,
    ) {}

    public function execute(int $paymentMethodId): void
    {
        $this->paymentMethods->delete(
            $this->paymentMethods->find($paymentMethodId),
        );
    }
}
