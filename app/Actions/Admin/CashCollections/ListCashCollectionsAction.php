<?php

namespace App\Actions\Admin\CashCollections;

use App\Data\Admin\ListQueryData;
use App\Repositories\CashCollectionRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListCashCollectionsAction
{
    public function __construct(
        private readonly CashCollectionRepository $collections,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->collections->paginate($query->search, $query->perPage);
    }
}
