<?php

namespace App\Actions\Rider\Dashboard;

use App\Models\Rider;
use App\Repositories\RiderRepository;

class ShowRiderDashboardAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    /** @return array<string, mixed> */
    public function execute(Rider $rider): array
    {
        return $this->riders->dashboardMetrics($rider);
    }
}
