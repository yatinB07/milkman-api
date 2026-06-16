<?php

namespace Database\Factories;

use App\Models\Faq;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Faq> */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'question' => fake()->sentence().'?',
            'answer' => fake()->paragraph(),
            'is_active' => true,
        ];
    }
}
