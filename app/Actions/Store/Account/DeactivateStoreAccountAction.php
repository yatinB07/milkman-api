<?php

namespace App\Actions\Store\Account;

use App\Models\Store;
use App\Repositories\StoreRepository;

class DeactivateStoreAccountAction
{
    public function __construct(private readonly StoreRepository $stores) {}

    public function execute(Store $store): Store
    {
        return $this->stores->deactivateAccount($store);
    }
}
