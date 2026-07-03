<?php

namespace App\Actions\Store\ProductImages;

use App\Data\Store\StoreProductImageData;
use App\Models\ProductImage;
use App\Models\Store;
use App\Repositories\ProductImageRepository;

class CreateStoreProductImageAction
{
    public function __construct(private readonly ProductImageRepository $images) {}

    public function execute(Store $store, StoreProductImageData $data): ProductImage
    {
        return $this->images->createForStore($store, $data->toArray());
    }
}
