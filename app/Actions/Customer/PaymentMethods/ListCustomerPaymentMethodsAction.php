<?php

namespace App\Actions\Customer\PaymentMethods;

use App\Data\Customer\ListCustomerQueryData;
use App\Repositories\PaymentMethodRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerPaymentMethodsAction
{
    public function __construct(private readonly PaymentMethodRepository $methods) {}

    public function execute(ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->methods->paginateVisibleActive($query->search, $query->perPage);
    }
}
