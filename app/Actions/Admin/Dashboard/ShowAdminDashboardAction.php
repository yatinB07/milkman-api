<?php

namespace App\Actions\Admin\Dashboard;

use App\Repositories\AdminDashboardRepository;

class ShowAdminDashboardAction
{
    public function __construct(private readonly AdminDashboardRepository $dashboard) {}

    /** @return array<string, mixed> */
    public function execute(): array
    {
        return $this->dashboard->metrics();
    }
}
