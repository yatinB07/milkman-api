<?php

namespace App\Actions\Store\TimeSlots;

use App\Data\Store\StoreTimeSlotData;
use App\Models\Store;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class UpdateStoreTimeSlotAction
{
    public function __construct(private readonly TimeSlotRepository $timeSlots) {}

    public function execute(Store $store, int $slotId, StoreTimeSlotData $data): TimeSlot
    {
        return $this->timeSlots->update(
            $this->timeSlots->findForStore($store, $slotId),
            $data->toArray(),
        );
    }
}
