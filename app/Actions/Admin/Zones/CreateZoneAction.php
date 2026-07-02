<?php

namespace App\Actions\Admin\Zones;

use App\Data\Admin\ZoneData;
use App\Models\Zone;
use App\Repositories\ZoneRepository;

class CreateZoneAction
{
    public function __construct(
        private readonly ZoneRepository $zones,
    ) {}

    public function execute(ZoneData $data): Zone
    {
        return $this->zones->create($data->toArray());
    }
}
