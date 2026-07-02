<?php

namespace App\Actions\Admin\Stores;

use App\Data\Admin\StoreData;
use App\Models\Store;
use App\Repositories\StoreRepository;

class UpdateStoreAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(int $storeId, StoreData $data): Store
    {
        return $this->stores->update(
            $this->stores->find($storeId),
            $data->toArray(),
        );
    }
}
