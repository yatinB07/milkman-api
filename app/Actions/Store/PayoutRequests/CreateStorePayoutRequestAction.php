<?php

namespace App\Actions\Store\PayoutRequests;

use App\Data\Store\StorePayoutRequestData;
use App\Enums\PayoutStatus;
use App\Exceptions\Catalog\StorePayoutExceedsAvailableEarningException;
use App\Models\PayoutRequest;
use App\Models\Store;
use App\Repositories\PayoutRequestRepository;
use App\Services\StorePayoutEligibilityService;

class CreateStorePayoutRequestAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
        private readonly StorePayoutEligibilityService $eligibility,
    ) {}

    public function execute(Store $store, StorePayoutRequestData $data): PayoutRequest
    {
        if (! $this->eligibility->canWithdraw($store, $data->amount())) {
            throw new StorePayoutExceedsAvailableEarningException;
        }

        return $this->payouts->createForStore($store, array_merge($data->toArray(), [
            'status' => PayoutStatus::Pending->value,
            'requested_at' => now(),
        ]));
    }
}
