<?php

namespace App\Actions\Admin\MilkData;

use App\Data\Admin\ListQueryData;
use App\Repositories\MilkDataRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListMilkDataAction
{
    public function __construct(private readonly MilkDataRepository $milkData) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->milkData->paginate($query->search, $query->perPage);
    }
}
