<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\StoreGalleryImage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<StoreGalleryImage> */
class StoreGalleryImageFactory extends Factory
{
    protected $model = StoreGalleryImage::class;

    public function definition(): array
    {
        return [
            'store_id' => Store::factory(),
            'image_path' => 'stores/gallery/'.fake()->uuid().'.png',
            'is_active' => true,
        ];
    }
}
