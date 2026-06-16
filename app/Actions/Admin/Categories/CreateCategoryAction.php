<?php

namespace App\Actions\Admin\Categories;

use App\Models\Category;
use App\Repositories\CategoryRepository;

class CreateCategoryAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    /** @param array<string, mixed> $attributes */
    public function execute(array $attributes): Category
    {
        return $this->categories->create($attributes);
    }
}
