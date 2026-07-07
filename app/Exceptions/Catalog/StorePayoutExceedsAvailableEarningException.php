<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class StorePayoutExceedsAvailableEarningException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.store_payout_exceeds_available_earning'));
    }
}
