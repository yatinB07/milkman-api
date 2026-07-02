<?php

namespace App\Actions\Admin\Products;

use App\Data\Admin\ProductData;
use App\Models\Product;
use App\Repositories\ProductRepository;

class UpdateProductAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(int $productId, ProductData $data): Product
    {
        return $this->products->update(
            $this->products->find($productId),
            $data->toArray(),
        );
    }
}
