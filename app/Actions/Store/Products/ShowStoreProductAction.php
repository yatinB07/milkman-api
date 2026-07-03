<?php

namespace App\Actions\Store\Products;

use App\Models\Product;
use App\Models\Store;
use App\Repositories\ProductRepository;

class ShowStoreProductAction
{
    public function __construct(private readonly ProductRepository $products) {}

    public function execute(Store $store, int $productId): Product
    {
        return $this->products->findForStore($store, $productId);
    }
}
