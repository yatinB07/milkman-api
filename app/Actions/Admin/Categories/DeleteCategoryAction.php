<?php

namespace App\Actions\Admin\Categories;

use App\Repositories\CategoryRepository;

class DeleteCategoryAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(int $categoryId): void
    {
        $this->categories->delete(
            $this->categories->find($categoryId),
        );
    }
}
