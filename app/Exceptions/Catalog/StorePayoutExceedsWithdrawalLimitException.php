<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class StorePayoutExceedsWithdrawalLimitException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.store_payout_exceeds_withdrawal_limit'));
    }
}
