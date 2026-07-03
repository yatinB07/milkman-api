<?php

namespace App\Actions\Store\Products;

use App\Data\Store\StoreProductData;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\ProductRepository;

class UpdateStoreProductAction
{
    public function __construct(private readonly ProductRepository $products) {}

    public function execute(Store $store, int $productId, StoreProductData $data): Product
    {
        return $this->products->update(
            $this->products->findForStore($store, $productId),
            $data->toArray(),
        );
    }
}
