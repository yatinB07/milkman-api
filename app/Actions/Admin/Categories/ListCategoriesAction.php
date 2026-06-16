<?php

namespace App\Actions\Admin\Categories;

use App\Repositories\CategoryRepository;
use Illuminate\Database\Eloquent\Collection;

class ListCategoriesAction
{
    public function __construct(
        private readonly CategoryRepository $categories,
    ) {}

    public function execute(): Collection
    {
        return $this->categories->all();
    }
}
