<?php

namespace App\Actions\Store\ProductImages;

use App\Data\Store\ListStoreQueryData;
use App\Models\ProductImage;
use App\Models\Store;
use App\Repositories\ProductImageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreProductImagesAction
{
    public function __construct(private readonly ProductImageRepository $images) {}

    /** @return LengthAwarePaginator<int, ProductImage> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->images->paginateForStore($store, $query->search, $query->perPage);
    }
}
