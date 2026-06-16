<?php

namespace Database\Factories;

use App\Models\SubscriptionOrder;
use App\Models\SubscriptionOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubscriptionOrderItem> */
class SubscriptionOrderItemFactory extends Factory
{
    protected $model = SubscriptionOrderItem::class;

    public function definition(): array
    {
        return [
            'subscription_order_id' => SubscriptionOrder::factory(),
            'quantity' => 1,
            'product_title' => fake()->words(3, true),
            'discount' => 0,
            'image_path' => 'products/'.fake()->uuid().'.png',
            'price' => 58,
            'variant_title' => '1 Litre',
            'starts_at' => now()->addDay()->toDateString(),
            'total_deliveries' => 10,
            'total_dates' => '[]',
            'completed_dates' => '[]',
            'selected_days' => '["mon","wed","fri"]',
            'time_slot' => '06:00 AM - 09:00 AM',
        ];
    }
}
