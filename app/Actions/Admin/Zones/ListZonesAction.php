<?php

namespace App\Actions\Admin\Zones;

use App\Data\Admin\ListQueryData;
use App\Repositories\ZoneRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListZonesAction
{
    public function __construct(
        private readonly ZoneRepository $zones,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->zones->paginate($query->search, $query->perPage, $query->isActive);
    }
}
