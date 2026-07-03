<?php

namespace App\Actions\Store\PayoutRequests;

use App\Models\PayoutRequest;
use App\Models\Store;
use App\Repositories\PayoutRequestRepository;

class ShowStorePayoutRequestAction
{
    public function __construct(private readonly PayoutRequestRepository $payouts) {}

    public function execute(Store $store, int $payoutId): PayoutRequest
    {
        return $this->payouts->findForStore($store, $payoutId);
    }
}
