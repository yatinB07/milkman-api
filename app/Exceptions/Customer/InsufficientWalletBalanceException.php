<?php

namespace App\Exceptions\Customer;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InsufficientWalletBalanceException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.customer_wallet_insufficient'));
    }
}
