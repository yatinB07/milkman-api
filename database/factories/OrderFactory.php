<?php

namespace Database\Factories;

use App\Models\Coupon;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'customer_id' => Customer::factory(),
            'ordered_at' => now(),
            'payment_method_id' => PaymentMethod::factory(),
            'address' => fake()->streetAddress(),
            'landmark' => fake()->secondaryAddress(),
            'delivery_charge' => 10,
            'coupon_id' => Coupon::factory(),
            'coupon_amount' => 10,
            'total' => 110,
            'subtotal' => 110,
            'transaction_id' => fake()->unique()->bothify('ORDER-####'),
            'admin_status' => 1,
            'rider_id' => Rider::factory(),
            'wallet_amount' => 0,
            'customer_name' => fake()->name(),
            'customer_mobile' => fake()->numerify('##########'),
            'status' => 'pending',
            'time_slot' => '06:00 AM - 09:00 AM',
            'order_type' => 'Delivery',
            'commission_percent' => 8,
            'store_charge' => 5,
            'internal_status' => 1,
        ];
    }
}
