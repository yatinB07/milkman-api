<?php

namespace App\Actions\Admin\MilkData;

use App\Models\MilkData;
use App\Repositories\MilkDataRepository;

class ShowMilkDataAction
{
    public function __construct(private readonly MilkDataRepository $milkData) {}

    public function execute(int $milkDataId): MilkData
    {
        return $this->milkData->find($milkDataId);
    }
}
