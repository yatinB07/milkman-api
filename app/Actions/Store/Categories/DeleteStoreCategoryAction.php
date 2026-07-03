<?php

namespace App\Actions\Store\Categories;

use App\Models\Store;
use App\Repositories\StoreCategoryRepository;

class DeleteStoreCategoryAction
{
    public function __construct(private readonly StoreCategoryRepository $categories) {}

    public function execute(Store $store, int $categoryId): void
    {
        $this->categories->delete($this->categories->findForStore($store, $categoryId));
    }
}
