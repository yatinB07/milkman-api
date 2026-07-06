<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionDeliveryDateAlreadyCompletedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.subscription_delivery_date_already_completed'));
    }
}
