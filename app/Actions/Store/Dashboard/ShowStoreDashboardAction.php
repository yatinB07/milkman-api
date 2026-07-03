<?php

namespace App\Actions\Store\Dashboard;

use App\Models\Store;
use App\Repositories\StoreRepository;

class ShowStoreDashboardAction
{
    public function __construct(private readonly StoreRepository $stores) {}

    /** @return array<string, mixed> */
    public function execute(Store $store): array
    {
        return $this->stores->dashboardMetrics($store);
    }
}
