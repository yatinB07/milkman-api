<?php

namespace App\Actions\Admin\PayoutRequests;

use App\Models\PayoutRequest;
use App\Repositories\PayoutRequestRepository;

class ShowPayoutRequestAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
    ) {}

    public function execute(int $payoutId): PayoutRequest
    {
        return $this->payouts->find($payoutId);
    }
}
