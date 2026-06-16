<?php

namespace App\Actions\Catalog;

use App\Models\Store;
use App\Repositories\CatalogRepository;

class GetPublicStoreDetailAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(int $storeId): Store
    {
        return $this->catalog->activeStoreDetail($storeId);
    }
}
