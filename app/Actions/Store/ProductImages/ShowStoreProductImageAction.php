<?php

namespace App\Actions\Store\ProductImages;

use App\Models\ProductImage;
use App\Models\Store;
use App\Repositories\ProductImageRepository;

class ShowStoreProductImageAction
{
    public function __construct(private readonly ProductImageRepository $images) {}

    public function execute(Store $store, int $imageId): ProductImage
    {
        return $this->images->findForStore($store, $imageId);
    }
}
