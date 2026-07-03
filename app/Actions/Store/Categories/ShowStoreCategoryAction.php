<?php

namespace App\Actions\Store\Categories;

use App\Models\Store;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class ShowStoreCategoryAction
{
    public function __construct(private readonly StoreCategoryRepository $categories) {}

    public function execute(Store $store, int $categoryId): StoreCategory
    {
        return $this->categories->findForStore($store, $categoryId);
    }
}
