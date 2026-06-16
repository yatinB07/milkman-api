<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<WalletTransaction> */
class WalletTransactionFactory extends Factory
{
    protected $model = WalletTransaction::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'message' => fake()->sentence(),
            'type' => fake()->randomElement(['credit', 'debit']),
            'amount' => 25,
            'transacted_at' => now(),
        ];
    }
}
