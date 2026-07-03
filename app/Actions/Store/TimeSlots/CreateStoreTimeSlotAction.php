<?php

namespace App\Actions\Store\TimeSlots;

use App\Data\Store\StoreTimeSlotData;
use App\Models\Store;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class CreateStoreTimeSlotAction
{
    public function __construct(private readonly TimeSlotRepository $timeSlots) {}

    public function execute(Store $store, StoreTimeSlotData $data): TimeSlot
    {
        return $this->timeSlots->createForStore($store, $data->toArray());
    }
}
