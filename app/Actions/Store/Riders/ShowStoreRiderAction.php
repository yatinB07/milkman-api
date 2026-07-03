<?php

namespace App\Actions\Store\Riders;

use App\Models\Rider;
use App\Models\Store;
use App\Repositories\RiderRepository;

class ShowStoreRiderAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function execute(Store $store, int $riderId): Rider
    {
        return $this->riders->findForStore($store, $riderId);
    }
}
