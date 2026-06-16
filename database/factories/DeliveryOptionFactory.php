<?php

namespace Database\Factories;

use App\Models\DeliveryOption;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeliveryOption> */
class DeliveryOptionFactory extends Factory
{
    protected $model = DeliveryOption::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'title' => fake()->randomElement(['Morning Delivery', 'Evening Delivery']),
            'delivery_days' => fake()->numberBetween(0, 3),
            'is_active' => true,
        ];
    }
}
