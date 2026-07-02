<?php

namespace App\Actions\Admin\CashCollections;

use App\Data\Admin\CashCollectionData;
use App\Models\CashCollection;
use App\Repositories\CashCollectionRepository;

class UpdateCashCollectionAction
{
    public function __construct(
        private readonly CashCollectionRepository $collections,
    ) {}

    public function execute(int $collectionId, CashCollectionData $data): CashCollection
    {
        return $this->collections->update(
            $this->collections->find($collectionId),
            $data->toArray(),
        );
    }
}
