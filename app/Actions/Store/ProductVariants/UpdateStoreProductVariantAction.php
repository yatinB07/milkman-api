<?php

namespace App\Actions\Store\ProductVariants;

use App\Data\Store\StoreProductVariantData;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Repositories\ProductVariantRepository;

class UpdateStoreProductVariantAction
{
    public function __construct(private readonly ProductVariantRepository $variants) {}

    public function execute(Store $store, int $variantId, StoreProductVariantData $data): ProductVariant
    {
        return $this->variants->update(
            $this->variants->findForStore($store, $variantId),
            $data->toArray(),
        );
    }
}
