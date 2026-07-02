<?php

namespace App\Actions\Catalog;

use App\Data\Catalog\PublicListQueryData;
use App\Repositories\CatalogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPublicStoreProductsAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(int $storeId, PublicListQueryData $query): LengthAwarePaginator
    {
        return $this->catalog->activeStoreProducts($storeId, $query);
    }
}
