<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SubscriptionDeliveryDateNotScheduledException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.subscription_delivery_date_not_scheduled'));
    }
}
