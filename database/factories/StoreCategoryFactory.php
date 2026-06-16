<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StoreCategory> */
class StoreCategoryFactory extends Factory
{
    protected $model = StoreCategory::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'title' => fake()->unique()->words(2, true),
            'image_path' => 'store-categories/'.fake()->uuid().'.png',
            'is_active' => true,
        ];
    }
}
