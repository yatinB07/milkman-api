<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'quantity' => 1,
            'product_title' => fake()->words(3, true),
            'discount' => 0,
            'image_path' => 'products/'.fake()->uuid().'.png',
            'price' => 60,
            'variant_title' => '1 Litre',
        ];
    }
}
