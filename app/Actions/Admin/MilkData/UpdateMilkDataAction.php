<?php

namespace App\Actions\Admin\MilkData;

use App\Data\Admin\MilkDataData;
use App\Models\MilkData;
use App\Repositories\MilkDataRepository;

class UpdateMilkDataAction
{
    public function __construct(private readonly MilkDataRepository $milkData) {}

    public function execute(int $milkDataId, MilkDataData $data): MilkData
    {
        return $this->milkData->update($this->milkData->find($milkDataId), $data->toArray());
    }
}
