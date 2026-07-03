<?php

namespace App\Actions\Store\PayoutRequests;

use App\Data\Store\ListStoreQueryData;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Repositories\PayoutRequestRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListStorePayoutRequestsAction
{
    public function __construct(private readonly PayoutRequestRepository $payouts) {}

    /** @return LengthAwarePaginator<int, PayoutRequest> */
    public function execute(Store $store, ListStoreQueryData $query): LengthAwarePaginator
    {
        return $this->payouts->paginateForStore($store, $query->search, $query->perPage);
    }
}
