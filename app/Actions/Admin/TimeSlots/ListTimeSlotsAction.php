<?php

namespace App\Actions\Admin\TimeSlots;

use App\Data\Admin\ListQueryData;
use App\Repositories\TimeSlotRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListTimeSlotsAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->timeSlots->paginate($query->search, $query->perPage);
    }
}
