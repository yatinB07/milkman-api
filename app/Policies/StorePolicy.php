<?php

namespace App\Policies;

use App\Models\Admin;
use App\Models\Store;
use Illuminate\Foundation\Auth\User as Authenticatable;

class StorePolicy
{
    public function view(Authenticatable $identity, Store $store): bool
    {
        return $this->canManageAnyStore($identity)
            || ($identity instanceof Store && $identity->is($store));
    }

    public function update(Authenticatable $identity, Store $store): bool
    {
        return $this->canManageAnyStore($identity)
            || ($identity instanceof Store && $identity->is($store) && $identity->can('stores.update'));
    }

    public function delete(Authenticatable $identity, Store $store): bool
    {
        return $this->canManageAnyStore($identity) && $identity->can('stores.delete');
    }

    private function canManageAnyStore(Authenticatable $identity): bool
    {
        return $identity instanceof Admin && $identity->can('stores.update');
    }
}
