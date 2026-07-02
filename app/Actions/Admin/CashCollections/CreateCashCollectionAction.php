<?php

namespace App\Actions\Admin\CashCollections;

use App\Data\Admin\CashCollectionData;
use App\Models\CashCollection;
use App\Repositories\CashCollectionRepository;

class CreateCashCollectionAction
{
    public function __construct(
        private readonly CashCollectionRepository $collections,
    ) {}

    public function execute(CashCollectionData $data): CashCollection
    {
        return $this->collections->create($data->toArray());
    }
}
