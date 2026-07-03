<?php

namespace App\Actions\Store\Categories;

use App\Data\Store\StoreCategoryData;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class UpdateStoreCategoryAction
{
    public function __construct(private readonly StoreCategoryRepository $categories) {}

    public function execute(Store $store, int $categoryId, StoreCategoryData $data): StoreCategory
    {
        return $this->categories->update(
            $this->categories->findForStore($store, $categoryId),
            $data->toArray(),
        );
    }
}
