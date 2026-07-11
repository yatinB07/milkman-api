<?php

namespace App\Actions\Admin\Categories;

use App\Data\Admin\ListQueryData;
use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCategoriesAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->categories->paginate($query->search, $query->perPage, $query->isActive);
    }
}
