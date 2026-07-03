<?php

namespace App\Actions\Store\ProductVariants;

use App\Data\Store\StoreProductVariantData;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Repositories\ProductVariantRepository;

class CreateStoreProductVariantAction
{
    public function __construct(private readonly ProductVariantRepository $variants) {}

    public function execute(Store $store, StoreProductVariantData $data): ProductVariant
    {
        return $this->variants->createForStore($store, $data->toArray());
    }
}
