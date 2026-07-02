<?php

namespace App\Actions\Admin\Riders;

use App\Data\Admin\RiderData;
use App\Models\Rider;
use App\Repositories\RiderRepository;

class CreateRiderAction
{
    public function __construct(
        private readonly RiderRepository $riders,
    ) {}

    public function execute(RiderData $data): Rider
    {
        return $this->riders->create($data->toArray());
    }
}
