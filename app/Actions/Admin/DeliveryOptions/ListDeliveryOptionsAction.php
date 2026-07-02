<?php

namespace App\Actions\Admin\DeliveryOptions;

use App\Data\Admin\ListQueryData;
use App\Repositories\DeliveryOptionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListDeliveryOptionsAction
{
    public function __construct(
        private readonly DeliveryOptionRepository $deliveryOptions,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->deliveryOptions->paginate($query->search, $query->perPage);
    }
}
