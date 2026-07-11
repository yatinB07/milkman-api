<?php

namespace App\Actions\Admin\StoreCategories;

use App\Data\Admin\ListQueryData;
use App\Repositories\StoreCategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoreCategoriesAction
{
    public function __construct(
        private readonly StoreCategoryRepository $storeCategories,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->storeCategories->paginate($query->search, $query->perPage, $query->isActive);
    }
}
