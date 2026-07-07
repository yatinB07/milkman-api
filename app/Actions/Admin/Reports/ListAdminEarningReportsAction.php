<?php

namespace App\Actions\Admin\Reports;

use App\Data\Admin\ListQueryData;
use App\Repositories\AdminEarningReportRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListAdminEarningReportsAction
{
    public function __construct(private readonly AdminEarningReportRepository $reports) {}

    public function execute(ListQueryData $data): LengthAwarePaginator
    {
        return $this->reports->paginate($data);
    }
}
