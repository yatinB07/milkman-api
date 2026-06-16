<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ProductVariant> */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'product_id' => Product::factory(),
            'subscribe_price' => 58,
            'normal_price' => 60,
            'title' => fake()->randomElement(['500 ml', '1 Litre', '2 Litres']),
            'discount' => 2,
            'is_out_of_stock' => false,
            'is_subscription_required' => false,
        ];
    }
}
