<?php

namespace App\Actions\Admin\Stores;

use App\Models\Store;
use App\Repositories\StoreRepository;

class ShowStoreAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(int $storeId): Store
    {
        return $this->stores->find($storeId);
    }
}
