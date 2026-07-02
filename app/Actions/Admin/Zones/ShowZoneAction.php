<?php

namespace App\Actions\Admin\Zones;

use App\Models\Zone;
use App\Repositories\ZoneRepository;

class ShowZoneAction
{
    public function __construct(
        private readonly ZoneRepository $zones,
    ) {}

    public function execute(int $zoneId): Zone
    {
        return $this->zones->find($zoneId);
    }
}
