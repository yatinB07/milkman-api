<?php

namespace App\Actions\Store\GalleryImages;

use App\Models\Store;
use App\Repositories\StoreGalleryImageRepository;

class DeleteStoreGalleryImageAction
{
    public function __construct(private readonly StoreGalleryImageRepository $images) {}

    public function execute(Store $store, int $imageId): void
    {
        $this->images->delete($this->images->findForStore($store, $imageId));
    }
}
