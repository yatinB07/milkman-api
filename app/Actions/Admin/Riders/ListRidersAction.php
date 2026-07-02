<?php

namespace App\Actions\Admin\Riders;

use App\Data\Admin\ListQueryData;
use App\Repositories\RiderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListRidersAction
{
    public function __construct(
        private readonly RiderRepository $riders,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->riders->paginate($query->search, $query->perPage);
    }
}
