<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ZoneNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('catalog.zone_not_found'));
    }
}
