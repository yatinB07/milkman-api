<?php

namespace App\Actions\Admin\StoreGalleryImages;

use App\Data\Admin\StoreGalleryImageData;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class UpdateStoreGalleryImageAction
{
    public function __construct(
        private readonly StoreGalleryImageRepository $images,
    ) {}

    public function execute(int $imageId, StoreGalleryImageData $data): StoreGalleryImage
    {
        return $this->images->update(
            $this->images->find($imageId),
            $data->toArray(),
        );
    }
}
