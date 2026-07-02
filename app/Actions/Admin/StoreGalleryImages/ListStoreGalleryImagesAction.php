<?php

namespace App\Actions\Admin\StoreGalleryImages;

use App\Data\Admin\ListQueryData;
use App\Repositories\StoreGalleryImageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreGalleryImagesAction
{
    public function __construct(
        private readonly StoreGalleryImageRepository $images,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->images->paginate($query->search, $query->perPage);
    }
}
