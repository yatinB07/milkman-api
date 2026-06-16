<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Favorite;
use App\Models\Store;
use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Favorite> */
class FavoriteFactory extends Factory
{
    protected $model = Favorite::class;

    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'store_id' => Store::factory(),
            'zone_id' => Zone::factory(),
        ];
    }
}
