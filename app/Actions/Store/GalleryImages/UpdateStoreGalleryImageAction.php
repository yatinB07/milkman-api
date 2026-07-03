<?php

namespace App\Actions\Store\GalleryImages;

use App\Data\Store\StoreGalleryImageData;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class UpdateStoreGalleryImageAction
{
    public function __construct(private readonly StoreGalleryImageRepository $images) {}

    public function execute(Store $store, int $imageId, StoreGalleryImageData $data): StoreGalleryImage
    {
        return $this->images->update(
            $this->images->findForStore($store, $imageId),
            $data->toArray(),
        );
    }
}
