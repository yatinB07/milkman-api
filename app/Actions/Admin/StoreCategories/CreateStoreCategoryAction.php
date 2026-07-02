<?php

namespace App\Actions\Admin\StoreCategories;

use App\Data\Admin\StoreCategoryData;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class CreateStoreCategoryAction
{
    public function __construct(
        private readonly StoreCategoryRepository $storeCategories,
    ) {}

    public function execute(StoreCategoryData $data): StoreCategory
    {
        return $this->storeCategories->create($data->toArray());
    }
}
