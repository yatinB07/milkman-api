<?php

namespace App\Actions\Admin\Zones;

use App\Repositories\ZoneRepository;

class DeleteZoneAction
{
    public function __construct(
        private readonly ZoneRepository $zones,
    ) {}

    public function execute(int $zoneId): void
    {
        $this->zones->delete(
            $this->zones->find($zoneId),
        );
    }
}
