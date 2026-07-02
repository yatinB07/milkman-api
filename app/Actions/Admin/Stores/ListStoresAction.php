<?php

namespace App\Actions\Admin\Stores;

use App\Data\Admin\ListQueryData;
use App\Repositories\StoreRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStoresAction
{
    public function __construct(
        private readonly StoreRepository $stores,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->stores->paginate($query->search, $query->perPage);
    }
}
