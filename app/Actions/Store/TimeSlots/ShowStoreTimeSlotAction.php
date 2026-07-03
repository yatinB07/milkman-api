<?php

namespace App\Actions\Store\TimeSlots;

use App\Models\Store;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class ShowStoreTimeSlotAction
{
    public function __construct(private readonly TimeSlotRepository $timeSlots) {}

    public function execute(Store $store, int $slotId): TimeSlot
    {
        return $this->timeSlots->findForStore($store, $slotId);
    }
}
