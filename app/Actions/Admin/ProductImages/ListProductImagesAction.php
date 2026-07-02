<?php

namespace App\Actions\Admin\ProductImages;

use App\Data\Admin\ListQueryData;
use App\Repositories\ProductImageRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductImagesAction
{
    public function __construct(
        private readonly ProductImageRepository $images,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->images->paginate($query->search, $query->perPage);
    }
}
