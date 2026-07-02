<?php

namespace App\Actions\Admin\TimeSlots;

use App\Data\Admin\TimeSlotData;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class UpdateTimeSlotAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    public function execute(int $timeSlotId, TimeSlotData $data): TimeSlot
    {
        return $this->timeSlots->update(
            $this->timeSlots->find($timeSlotId),
            $data->toArray(),
        );
    }
}
