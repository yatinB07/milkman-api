<?php

namespace App\Actions\Store\DeliveryOptions;

use App\Data\Store\ListStoreQueryData;
use App\Models\DeliveryOption;
use App\Models\Store;
use App\Repositories\DeliveryOptionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreDeliveryOptionsAction
{
    public function __construct(private readonly DeliveryOptionRepository $deliveryOptions) {}

    /** @return LengthAwarePaginator<int, DeliveryOption> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->deliveryOptions->paginateForStore($store, $query->search, $query->perPage);
    }
}
