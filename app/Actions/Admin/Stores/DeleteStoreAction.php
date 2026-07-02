<?php

namespace App\Actions\Admin\Stores;

use App\Repositories\StoreRepository;

class DeleteStoreAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(int $storeId): void
    {
        $this->stores->delete(
            $this->stores->find($storeId),
        );
    }
}
