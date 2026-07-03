<?php

namespace App\Actions\Store\Riders;

use App\Models\Store;
use App\Repositories\RiderRepository;

class DeleteStoreRiderAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function execute(Store $store, int $riderId): void
    {
        $this->riders->delete($this->riders->findForStore($store, $riderId));
    }
}
