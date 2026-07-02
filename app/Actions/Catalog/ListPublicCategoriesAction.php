<?php

namespace App\Actions\Catalog;

use App\Data\Catalog\PublicListQueryData;
use App\Repositories\CatalogRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPublicCategoriesAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(PublicListQueryData $query): LengthAwarePaginator
    {
        return $this->catalog->activeCategories($query);
    }
}
