<?php

namespace App\Actions\Admin\StoreGalleryImages;

use App\Data\Admin\StoreGalleryImageData;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class CreateStoreGalleryImageAction
{
    public function __construct(
        private readonly StoreGalleryImageRepository $images,
    ) {}

    public function execute(StoreGalleryImageData $data): StoreGalleryImage
    {
        return $this->images->create($data->toArray());
    }
}
