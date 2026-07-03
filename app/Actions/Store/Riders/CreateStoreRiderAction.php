<?php

namespace App\Actions\Store\Riders;

use App\Data\Store\StoreRiderData;
use App\Models\Rider;
use App\Models\Store;
use App\Repositories\RiderRepository;

class CreateStoreRiderAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function execute(Store $store, StoreRiderData $data): Rider
    {
        $rider = $this->riders->createForStore($store, $data->toArray());
        $rider->assignRole('rider');

        return $rider;
    }
}
