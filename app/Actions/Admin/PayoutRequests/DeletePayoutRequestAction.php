<?php

namespace App\Actions\Admin\PayoutRequests;

use App\Repositories\PayoutRequestRepository;

class DeletePayoutRequestAction
{
    public function __construct(
        private readonly PayoutRequestRepository $payouts,
    ) {}

    public function execute(int $payoutId): void
    {
        $this->payouts->delete(
            $this->payouts->find($payoutId),
        );
    }
}
