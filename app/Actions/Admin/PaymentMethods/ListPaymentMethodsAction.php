<?php

namespace App\Actions\Admin\PaymentMethods;

use App\Data\Admin\ListQueryData;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPaymentMethodsAction
{
    public function __construct(
        private readonly PaymentMethodRepository $paymentMethods,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->paymentMethods->paginate($query->search, $query->perPage);
    }
}
