<?php

namespace App\Actions\Store\Categories;

use App\Data\Store\StoreCategoryData;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class CreateStoreCategoryAction
{
    public function __construct(private readonly StoreCategoryRepository $categories) {}

    public function execute(Store $store, StoreCategoryData $data): StoreCategory
    {
        return $this->categories->createForStore($store, $data->toArray());
    }
}
