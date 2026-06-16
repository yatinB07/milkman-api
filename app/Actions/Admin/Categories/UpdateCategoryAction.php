<?php

namespace App\Actions\Admin\Categories;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class UpdateCategoryAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(int $categoryId, array $attributes): Category
    {
        return $this->categories->update(
            $this->categories->find($categoryId),
            $attributes,
        );
    }
}
