<?php

namespace App\Actions\Customer\Availability;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerTimeSlotsAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    /** @return LengthAwarePaginator<int, TimeSlot> */
    public function execute(int $storeId, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->timeSlots->paginateActiveForStore($storeId, $query->search, $query->perPage);
    }
}
