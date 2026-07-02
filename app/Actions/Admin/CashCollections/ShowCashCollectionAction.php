<?php

namespace App\Actions\Admin\CashCollections;

use App\Models\CashCollection;
use App\Repositories\CashCollectionRepository;

class ShowCashCollectionAction
{
    public function __construct(
        private readonly CashCollectionRepository $collections,
    ) {}

    public function execute(int $collectionId): CashCollection
    {
        return $this->collections->find($collectionId);
    }
}
