<?php

namespace App\Actions\Store\ProductVariants;

use App\Models\Store;
use App\Repositories\ProductVariantRepository;

class DeleteStoreProductVariantAction
{
    public function __construct(private readonly ProductVariantRepository $variants) {}

    public function execute(Store $store, int $variantId): void
    {
        $this->variants->delete($this->variants->findForStore($store, $variantId));
    }
}
