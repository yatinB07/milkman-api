<?php

namespace App\Actions\Catalog;

use App\Repositories\CatalogRepository;
use Illuminate\Database\Eloquent\Collection;

class ListPublicCategoriesAction
{
    public function __construct(
        private readonly CatalogRepository $catalog,
    ) {}

    public function execute(): Collection
    {
        return $this->catalog->activeCategories();
    }
}
