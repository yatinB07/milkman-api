<?php

namespace App\Actions\Store\GalleryImages;

use App\Data\Store\ListStoreQueryData;
use App\Models\Store;
use App\Models\StoreGalleryImage;
use App\Repositories\StoreGalleryImageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreGalleryImagesAction
{
    public function __construct(private readonly StoreGalleryImageRepository $images) {}

    /** @return LengthAwarePaginator<int, StoreGalleryImage> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->images->paginateForStore($store, $query->search, $query->perPage);
    }
}
