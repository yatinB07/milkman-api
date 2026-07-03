<?php

namespace App\Actions\Store\Riders;

use App\Data\Store\StoreRiderData;
use App\Models\Rider;
use App\Models\Store;
use App\Repositories\RiderRepository;

class UpdateStoreRiderAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function execute(Store $store, int $riderId, StoreRiderData $data): Rider
    {
        return $this->riders->update(
            $this->riders->findForStore($store, $riderId),
            $data->toArray(),
        );
    }
}
