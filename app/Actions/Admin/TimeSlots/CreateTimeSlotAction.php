<?php

namespace App\Actions\Admin\TimeSlots;

use App\Data\Admin\TimeSlotData;
use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class CreateTimeSlotAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    public function execute(TimeSlotData $data): TimeSlot
    {
        return $this->timeSlots->create($data->toArray());
    }
}
