<?php

namespace App\Actions\Store\ProductVariants;

use App\Data\Store\ListStoreQueryData;
use App\Models\ProductVariant;
use App\Models\Store;
use App\Repositories\ProductVariantRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreProductVariantsAction
{
    public function __construct(private readonly ProductVariantRepository $variants) {}

    /** @return LengthAwarePaginator<int, ProductVariant> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->variants->paginateForStore($store, $query->search, $query->perPage);
    }
}
