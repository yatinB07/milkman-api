<?php

namespace Database\Factories;

use App\Models\PayoutRequest;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<PayoutRequest> */
class PayoutRequestFactory extends Factory
{
    protected $model = PayoutRequest::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'amount' => 250,
            'status' => 'pending',
            'proof_path' => null,
            'requested_at' => now(),
            'request_type' => 'bank',
            'account_number' => fake()->numerify('##########'),
            'bank_name' => 'Demo Bank',
            'account_name' => fake()->company(),
            'ifsc_code' => 'DEMO0001234',
            'upi_id' => fake()->userName().'@upi',
            'paypal_id' => fake()->safeEmail(),
        ];
    }
}
