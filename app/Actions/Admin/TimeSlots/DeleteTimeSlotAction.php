<?php

namespace App\Actions\Admin\TimeSlots;

use App\Repositories\TimeSlotRepository;

class DeleteTimeSlotAction
{
    public function __construct(
        private readonly TimeSlotRepository $timeSlots,
    ) {}

    public function execute(int $timeSlotId): void
    {
        $this->timeSlots->delete(
            $this->timeSlots->find($timeSlotId),
        );
    }
}
