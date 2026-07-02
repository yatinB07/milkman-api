<?php

namespace App\Actions\Admin\MilkData;

use App\Data\Admin\MilkDataData;
use App\Models\MilkData;
use App\Repositories\MilkDataRepository;

class CreateMilkDataAction
{
    public function __construct(private readonly MilkDataRepository $milkData) {}

    public function execute(MilkDataData $data): MilkData
    {
        return $this->milkData->create($data->toArray());
    }
}
