<?php

namespace App\Actions\Admin\PayoutRequests;

use App\Data\Admin\ListQueryData;
use App\Repositories\PayoutRequestRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListPayoutRequestsAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
    ) {}

    public function execute(ListQueryData $query): LengthAwarePaginator
    {
        return $this->payouts->paginate($query->search, $query->perPage);
    }
}
