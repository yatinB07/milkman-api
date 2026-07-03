<?php

namespace App\Actions\Store\ProductImages;

use App\Data\Store\StoreProductImageData;
use App\Models\ProductImage;
use App\Models\Store;
use App\Repositories\ProductImageRepository;

class UpdateStoreProductImageAction
{
    public function __construct(private readonly ProductImageRepository $images) {}

    public function execute(Store $store, int $imageId, StoreProductImageData $data): ProductImage
    {
        return $this->images->update(
            $this->images->findForStore($store, $imageId),
            $data->toArray(),
        );
    }
}
