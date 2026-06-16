<?php

namespace App\Actions\Admin\Categories;

use App\Repositories\CategoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCategoriesAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return $this->categories->paginate($search, $perPage);
    }
}
