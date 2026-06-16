<?php

namespace Database\Factories;

use App\Models\Zone;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Zone> */
class ZoneFactory extends Factory
{
    protected $model = Zone::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->city(),
            'is_active' => true,
            'coordinates' => '[{"lat":23.0225,"lng":72.5714},{"lat":23.0325,"lng":72.5814}]',
            'alias' => fake()->unique()->slug(2),
        ];
    }
}
