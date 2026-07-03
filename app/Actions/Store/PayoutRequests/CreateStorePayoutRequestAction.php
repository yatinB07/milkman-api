<?php

namespace App\Actions\Store\PayoutRequests;

use App\Data\Store\StorePayoutRequestData;
use App\Enums\PayoutStatus;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Repositories\PayoutRequestRepository;

class CreateStorePayoutRequestAction
{
    public function __construct(private readonly PayoutRequestRepository $payouts) {}

    public function execute(Store $store, StorePayoutRequestData $data): PayoutRequest
    {
        return $this->payouts->createForStore($store, array_merge($data->toArray(), [
            'status' => PayoutStatus::Pending->value,
            'requested_at' => now(),
        ]));
    }
}
