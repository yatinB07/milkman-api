<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class FutureSubscriptionDeliveryDateException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.future_subscription_delivery_date'));
    }
}
