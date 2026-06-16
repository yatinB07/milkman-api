<?php

namespace App\Actions\Catalog;

use App\Repositories\CatalogRepository;
use Illuminate\Database\Eloquent\Collection;

class ListPublicStoreProductsAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(int $storeId): Collection
    {
        return $this->catalog->activeStoreProducts($storeId);
    }
}
