<?php

namespace App\Actions\Admin\StoreCategories;

use App\Data\Admin\StoreCategoryData;
use App\Models\StoreCategory;
use App\Repositories\StoreCategoryRepository;

class UpdateStoreCategoryAction
{
    public function __construct(
        private readonly StoreCategoryRepository $storeCategories,
    ) {}

    public function execute(int $storeCategoryId, StoreCategoryData $data): StoreCategory
    {
        return $this->storeCategories->update(
            $this->storeCategories->find($storeCategoryId),
            $data->toArray(),
        );
    }
}
