<?php

namespace App\Events;

use App\Models\PayoutRequest;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PayoutRequested
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public PayoutRequest $payout,
    ) {}
}
