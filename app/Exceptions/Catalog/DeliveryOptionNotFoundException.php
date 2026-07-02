<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeliveryOptionNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('catalog.delivery_option_not_found'));
    }
}
