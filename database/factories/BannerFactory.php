<?php

namespace Database\Factories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Banner> */
class BannerFactory extends Factory
{
    protected $model = Banner::class;

    public function definition(): array
    {
        return [
            'title' => fake()->words(3, true),
            'image_path' => 'banners/'.fake()->uuid().'.png',
            'is_active' => true,
        ];
    }
}
