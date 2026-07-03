<?php

namespace App\Actions\Customer\Products;

use App\Data\Customer\ListCustomerQueryData;
use App\Models\Product;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCustomerStoreProductsAction
{
    public function __construct(
        private readonly ProductRepository $products,
    ) {}

    /** @return LengthAwarePaginator<int, Product> */
    public function execute(int $storeId, ListCustomerQueryData $query): LengthAwarePaginator
    {
        return $this->products->paginateActiveForStore($storeId, $query->search, $query->perPage);
    }
}
