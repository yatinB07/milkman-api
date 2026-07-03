<?php

namespace App\Actions\Customer\Products;

use App\Models\Product;
use App\Repositories\ProductRepository;

class ShowCustomerProductAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(int $productId): Product
    {
        return $this->products->findActiveForCustomer($productId);
    }
}
