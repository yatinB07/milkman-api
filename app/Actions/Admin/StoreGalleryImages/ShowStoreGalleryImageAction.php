<?php

namespace App\Actions\Admin\StoreGalleryImages;

use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class ShowStoreGalleryImageAction
{
    public function __construct(
        private readonly StoreGalleryImageRepository $images,
    ) {}

    public function execute(int $imageId): StoreGalleryImage
    {
        return $this->images->find($imageId);
    }
}
