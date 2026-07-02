<?php

namespace App\Actions\Admin\StoreCategories;

use App\Repositories\StoreCategoryRepository;

class DeleteStoreCategoryAction
{
    public function __construct(
        private readonly StoreCategoryRepository $storeCategories,
    ) {}

    public function execute(int $storeCategoryId): void
    {
        $this->storeCategories->delete(
            $this->storeCategories->find($storeCategoryId),
        );
    }
}
