<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StoreNotification> */
class StoreNotificationFactory extends Factory
{
    protected $model = StoreNotification::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'notified_at' => now(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
        ];
    }
}
