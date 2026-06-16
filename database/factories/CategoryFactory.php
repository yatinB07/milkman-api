<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Category> */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'title' => fake()->unique()->words(2, true),
            'image_path' => 'categories/'.fake()->uuid().'.png',
            'cover_path' => 'categories/covers/'.fake()->uuid().'.png',
            'is_active' => true,
        ];
    }
}
