<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\TimeSlot;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TimeSlot> */
class TimeSlotFactory extends Factory
{
    protected $model = TimeSlot::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'starts_at' => '06:00:00',
            'ends_at' => '09:00:00',
            'is_active' => true,
        ];
    }
}
