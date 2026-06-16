<?php

namespace Database\Factories;

use App\Models\Rider;
use App\Models\RiderNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RiderNotification> */
class RiderNotificationFactory extends Factory
{
    protected $model = RiderNotification::class;

    public function definition(): array
    {
        return [
            'rider_id' => Rider::factory(),
            'notified_at' => now(),
            'title' => fake()->sentence(3),
            'message' => fake()->sentence(),
        ];
    }
}
