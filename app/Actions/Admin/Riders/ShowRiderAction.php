<?php

namespace App\Actions\Admin\Riders;

use App\Models\Rider;
use App\Repositories\RiderRepository;

class ShowRiderAction
{
    public function __construct(
        private readonly RiderRepository $riders,
    ) {}

    public function execute(int $riderId): Rider
    {
        return $this->riders->find($riderId);
    }
}
