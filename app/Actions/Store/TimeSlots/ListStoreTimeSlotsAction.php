<?php

namespace App\Actions\Store\TimeSlots;

use App\Data\Store\ListStoreQueryData;
use App\Models\Store;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreTimeSlotsAction
{
    public function __construct(private readonly TimeSlotRepository $timeSlots) {}

    /** @return LengthAwarePaginator<int, TimeSlot> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->timeSlots->paginateForStore($store, $query->search, $query->perPage);
    }
}
