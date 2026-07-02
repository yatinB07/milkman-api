<?php

namespace App\Actions\Admin\Categories;

use App\Data\Admin\CategoryData;
use App\Models\Category;
use App\Repositories\CategoryRepository;

class UpdateCategoryAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(int $categoryId, CategoryData $data): Category
    {
        return $this->categories->update(
            $this->categories->find($categoryId),
            $data->toArray(),
        );
    }
}
