<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Coupon> */
class CouponFactory extends Factory
{
    protected $model = Coupon::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'image_path' => 'coupons/'.fake()->uuid().'.png',
            'title' => fake()->words(2, true),
            'code' => fake()->unique()->bothify('MILK###'),
            'subtitle' => fake()->sentence(3),
            'expires_at' => now()->addMonth()->toDateString(),
            'minimum_amount' => 100,
            'value' => 10,
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
