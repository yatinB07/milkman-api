<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Store;
use App\Models\StoreCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'store_category_id' => StoreCategory::factory(),
            'title' => fake()->unique()->words(3, true),
            'image_path' => 'products/'.fake()->uuid().'.png',
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
