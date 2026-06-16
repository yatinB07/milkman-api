<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerNotification;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CustomerNotification> */
class CustomerNotificationFactory extends Factory
{
    protected $model = CustomerNotification::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'notified_at' => now(),
            'title' => fake()->sentence(3),
            'description' => fake()->sentence(),
        ];
    }
}
