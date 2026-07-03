<?php

namespace App\Actions\Store\Riders;

use App\Data\Store\ListStoreQueryData;
use App\Models\Rider;
use App\Models\Store;
use App\Repositories\RiderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreRidersAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    /** @return LengthAwarePaginator<int, Rider> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->riders->paginateForStore($store, $query->search, $query->perPage);
    }
}
