<?php

namespace App\Actions\Admin\Zones;

use App\Data\Admin\ZoneData;
use App\Models\Zone;
use App\Repositories\ZoneRepository;

class UpdateZoneAction
{
    public function __construct(
        private readonly ZoneRepository $zones,
    ) {}

    public function execute(int $zoneId, ZoneData $data): Zone
    {
        return $this->zones->update(
            $this->zones->find($zoneId),
            $data->toArray(),
        );
    }
}
