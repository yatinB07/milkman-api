<?php

namespace App\Actions\Admin\Categories;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class ShowCategoryAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(int $categoryId): Category
    {
        return $this->categories->find($categoryId);
    }
}
