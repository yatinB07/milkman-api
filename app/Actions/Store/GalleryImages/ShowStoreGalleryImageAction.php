<?php

namespace App\Actions\Store\GalleryImages;

use App\Models\Store;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;

class ShowStoreGalleryImageAction
{
    public function __construct(private readonly StoreGalleryImageRepository $images) {}

    public function execute(Store $store, int $imageId): StoreGalleryImage
    {
        return $this->images->findForStore($store, $imageId);
    }
}
