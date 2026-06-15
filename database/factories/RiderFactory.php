<?php

namespace Database\Factories;

use App\Models\Rider;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Rider> */
class RiderFactory extends Factory
{
    protected $model = Rider::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'country_code' => '+1',
            'mobile' => fake()->unique()->numerify('##########'),
            'password' => 'password',
            'is_active' => true,
            'joined_at' => now()->toDateString(),
        ];
    }
}
