<?php

namespace App\Actions\Store\Products;

use App\Data\Store\ListStoreQueryData;
use App\Models\Product;
use App\Models\Store;
use App\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreProductsAction
{
    public function __construct(private readonly ProductRepository $products) {}

    /** @return LengthAwarePaginator<int, Product> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->products->paginateForStore($store, $query->search, $query->perPage);
    }
}
