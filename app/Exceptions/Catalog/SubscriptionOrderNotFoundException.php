<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SubscriptionOrderNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('catalog.subscription_order_not_found'));
    }
}
