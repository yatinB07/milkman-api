<?php

namespace App\Exceptions\Customer;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionScheduleDateNotFoundException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.subscription_schedule_date_not_found'));
    }
}
