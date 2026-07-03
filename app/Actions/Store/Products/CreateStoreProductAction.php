<?php

namespace App\Actions\Store\Products;

use App\Data\Store\StoreProductData;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\ProductRepository;

class CreateStoreProductAction
{
    public function __construct(private readonly ProductRepository $products) {}

    public function execute(Store $store, StoreProductData $data): Product
    {
        return $this->products->createForStore($store, $data->toArray());
    }
}
