<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class DeliveryOrderRequiredException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.delivery_order_required'));
    }
}
