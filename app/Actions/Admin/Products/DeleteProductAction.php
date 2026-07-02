<?php

namespace App\Actions\Admin\Products;

use App\Repositories\ProductRepository;

class DeleteProductAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    public function execute(int $productId): void
    {
        $this->products->delete(
            $this->products->find($productId),
        );
    }
}
