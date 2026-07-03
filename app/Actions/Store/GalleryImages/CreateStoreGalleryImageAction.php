<?php

namespace App\Actions\Store\GalleryImages;

use App\Data\Store\StoreGalleryImageData;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class CreateStoreGalleryImageAction
{
    public function __construct(private readonly StoreGalleryImageRepository $images) {}

    public function execute(Store $store, StoreGalleryImageData $data): StoreGalleryImage
    {
        return $this->images->createForStore($store, $data->toArray());
    }
}
