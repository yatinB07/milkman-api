<?php

namespace Database\Factories;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PaymentMethod> */
class PaymentMethodFactory extends Factory
{
    protected $model = PaymentMethod::class;

    public function definition(): array
    {
        return [
            'title' => fake()->randomElement(['Cash on Delivery', 'Wallet', 'Card']),
            'image_path' => 'payments/'.fake()->uuid().'.png',
            'attributes' => ['code' => fake()->slug()],
            'subtitle' => fake()->sentence(4),
            'is_visible' => true,
            'is_active' => true,
        ];
    }
}
