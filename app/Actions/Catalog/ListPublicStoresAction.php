<?php

namespace App\Actions\Catalog;

use App\Repositories\CatalogRepository;
use Illuminate\Database\Eloquent\Collection;

class ListPublicStoresAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(?string $search = null): Collection
    {
        return $this->catalog->activeStores($search);
    }
}
