<?php

namespace App\Actions\Admin\PayoutRequests;

use App\Data\Admin\PayoutRequestData;
use App\Models\PayoutRequest;
use App\Repositories\PayoutRequestRepository;

class UpdatePayoutRequestAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
    ) {}

    public function execute(int $payoutId, PayoutRequestData $data): PayoutRequest
    {
        return $this->payouts->update(
            $this->payouts->find($payoutId),
            $data->toArray(),
        );
    }
}
