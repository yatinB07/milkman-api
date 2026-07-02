<?php

namespace App\Actions\Admin\MilkData;

use App\Repositories\MilkDataRepository;

class DeleteMilkDataAction
{
    public function __construct(private readonly MilkDataRepository $milkData) {}

    public function execute(int $milkDataId): void
    {
        $this->milkData->delete($this->milkData->find($milkDataId));
    }
}
