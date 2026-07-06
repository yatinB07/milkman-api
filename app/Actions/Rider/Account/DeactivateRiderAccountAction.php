<?php

namespace App\Actions\Rider\Account;

use App\Models\Rider;
use App\Repositories\RiderRepository;

class DeactivateRiderAccountAction
{
    public function __construct(private readonly RiderRepository $riders) {}

    public function execute(Rider $rider): Rider
    {
        return $this->riders->deactivateAccount($rider);
    }
}
