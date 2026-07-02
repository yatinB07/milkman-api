<?php

namespace App\Actions\Admin\Products;

use App\Data\Admin\ProductData;
use App\Models\Product;
use App\Repositories\ProductRepository;

class CreateProductAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(ProductData $data): Product
    {
        return $this->products->create($data->toArray());
    }
}
