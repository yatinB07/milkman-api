<?php

namespace Database\Factories;

use App\Models\MilkData;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<MilkData> */
class MilkDataFactory extends Factory
{
    protected $model = MilkData::class;

    public function definition(): array
    {
        return [
            'data' => json_encode([
                'source' => 'factory',
                'note' => fake()->sentence(),
            ], JSON_THROW_ON_ERROR),
        ];
    }
}
