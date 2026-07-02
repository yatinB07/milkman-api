<?php

namespace App\Actions\Admin\StoreCategories;

use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class ShowStoreCategoryAction
{
    public function __construct(
        private readonly StoreCategoryRepository $storeCategories,
    ) {}

    public function execute(int $storeCategoryId): StoreCategory
    {
        return $this->storeCategories->find($storeCategoryId);
    }
}
