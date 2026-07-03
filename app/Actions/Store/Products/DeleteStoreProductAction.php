<?php

namespace App\Actions\Store\Products;

use App\Models\Store;
use App\Repositories\ProductRepository;

class DeleteStoreProductAction
{
    public function __construct(private readonly ProductRepository $products) {}

    public function execute(Store $store, int $productId): void
    {
        $this->products->delete($this->products->findForStore($store, $productId));
    }
}
