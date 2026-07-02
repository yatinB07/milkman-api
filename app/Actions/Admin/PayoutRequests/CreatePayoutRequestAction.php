<?php

namespace App\Actions\Admin\PayoutRequests;

use App\Data\Admin\PayoutRequestData;
use App\Models\PayoutRequest;
use App\Repositories\PayoutRequestRepository;

class CreatePayoutRequestAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
    ) {}

    public function execute(PayoutRequestData $data): PayoutRequest
    {
        return $this->payouts->create($data->toArray());
    }
}
