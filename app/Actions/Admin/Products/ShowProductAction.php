<?php

namespace App\Actions\Admin\Products;

use App\Models\Product;
use App\Repositories\ProductRepository;

class ShowProductAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(int $productId): Product
    {
        return $this->products->find($productId);
    }
}
