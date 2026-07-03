<?php

namespace App\Actions\Store\ProductVariants;

use App\Models\ProductVariant;
use App\Models\Store;
use App\Repositories\ProductVariantRepository;

class ShowStoreProductVariantAction
{
    public function __construct(private readonly ProductVariantRepository $variants) {}

    public function execute(Store $store, int $variantId): ProductVariant
    {
        return $this->variants->findForStore($store, $variantId);
    }
}
