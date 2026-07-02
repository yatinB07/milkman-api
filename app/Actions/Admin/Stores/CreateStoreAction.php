<?php

namespace App\Actions\Admin\Stores;

use App\Data\Admin\StoreData;
use App\Models\Store;
use App\Repositories\StoreRepository;

class CreateStoreAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(StoreData $data): Store
    {
        return $this->stores->create($data->toArray());
    }
}
