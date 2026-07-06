<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SelfPickupOrderRequiredException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.self_pickup_order_required'));
    }
}
