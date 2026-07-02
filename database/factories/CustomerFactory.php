<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Customer> */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'profile_image_path' => null,
            'email' => fake()->unique()->safeEmail(),
            'country_code' => '+1',
            'mobile' => fake()->unique()->numerify('##########'),
            'password' => 'password',
            'referral_code' => fake()->unique()->numerify('######'),
            'parent_referral_code' => null,
            'wallet_balance' => 0,
            'is_active' => true,
            'email_verified_at' => now(),
        ];
    }
}
