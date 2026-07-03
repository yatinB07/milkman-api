<?php

namespace App\Actions\Customer\Availability;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\DeliveryOption;
use App\Repositories\DeliveryOptionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerDeliveryOptionsAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    /** @return LengthAwarePaginator<int, DeliveryOption> */
    public function execute(int $storeId, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->deliveryOptions->paginateActiveForStore($storeId, $query->search, $query->perPage);
    }
}
