<?php

namespace App\Actions\Admin\CashCollections;

use App\Repositories\CashCollectionRepository;

class DeleteCashCollectionAction
{
    public function __construct(
        private readonly CashCollectionRepository $collections,
    ) {}

    public function execute(int $collectionId): void
    {
        $this->collections->delete(
            $this->collections->find($collectionId),
        );
    }
}
