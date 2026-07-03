<?php

namespace App\Actions\Store\TimeSlots;

use App\Models\Store;
use App\Repositories\TimeSlotRepository;

class DeleteStoreTimeSlotAction
{
    public function __construct(private readonly TimeSlotRepository $timeSlots) {}

    public function execute(Store $store, int $slotId): void
    {
        $this->timeSlots->delete($this->timeSlots->findForStore($store, $slotId));
    }
}
