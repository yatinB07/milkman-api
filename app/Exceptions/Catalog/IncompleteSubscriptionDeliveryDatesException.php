<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class IncompleteSubscriptionDeliveryDatesException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.incomplete_subscription_delivery_dates'));
    }
}
