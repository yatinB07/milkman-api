<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Store> */
class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition(): array
    {
        return [
            'title' => fake()->company(),
            'email' => fake()->unique()->companyEmail(),
            'password' => 'password',
            'country_code' => '+1',
            'mobile' => fake()->unique()->numerify('##########'),
            'full_address' => fake()->streetAddress(),
            'pincode' => fake()->postcode(),
            'landmark' => fake()->secondaryAddress(),
            'latitude' => fake()->latitude(),
            'longitude' => fake()->longitude(),
            'store_charge' => 0,
            'delivery_charge' => 2.50,
            'minimum_order_amount' => 10,
            'commission_percent' => 5,
            'opens_at' => '08:00:00',
            'closes_at' => '20:00:00',
            'is_pickup_enabled' => true,
            'is_active' => true,
        ];
    }
}
