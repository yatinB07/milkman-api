<?php

namespace App\Actions\Admin\TimeSlots;

use App\Models\TimeSlot;
use App\Repositories\TimeSlotRepository;

class ShowTimeSlotAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    public function execute(int $timeSlotId): TimeSlot
    {
        return $this->timeSlots->find($timeSlotId);
    }
}
