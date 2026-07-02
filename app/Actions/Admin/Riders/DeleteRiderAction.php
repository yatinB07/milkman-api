<?php

namespace App\Actions\Admin\Riders;

use App\Repositories\RiderRepository;

class DeleteRiderAction
{
    public function __construct(
        private readonly RiderRepository $riders,
    ) {}

    public function execute(int $riderId): void
    {
        $this->riders->delete(
            $this->riders->find($riderId),
        );
    }
}
