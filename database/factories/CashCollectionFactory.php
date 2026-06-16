<?php

namespace Database\Factories;

use App\Models\CashCollection;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<CashCollection> */
class CashCollectionFactory extends Factory
{
    protected $model = CashCollection::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'amount' => 110,
            'message' => fake()->sentence(),
            'collected_at' => now(),
        ];
    }
}
