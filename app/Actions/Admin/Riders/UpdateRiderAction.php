<?php

namespace App\Actions\Admin\Riders;

use App\Data\Admin\RiderData;
use App\Models\Rider;
use App\Repositories\RiderRepository;

class UpdateRiderAction
{
    public function __construct(
        private readonly RiderRepository $riders,
    ) {}

    public function execute(int $riderId, RiderData $data): Rider
    {
        return $this->riders->update(
            $this->riders->find($riderId),
            $data->toArray(),
        );
    }
}
