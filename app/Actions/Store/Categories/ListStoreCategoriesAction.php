<?php

namespace App\Actions\Store\Categories;

use App\Data\Store\ListStoreQueryData;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreCategoriesAction
{
    public function __construct(private readonly StoreCategoryRepository $categories) {}

    /** @return LengthAwarePaginator<int, StoreCategory> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->categories->paginateForStore($store, $query->search, $query->perPage);
    }
}
