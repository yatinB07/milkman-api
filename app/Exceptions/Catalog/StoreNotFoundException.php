<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StoreNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('catalog.store_not_found'));
    }
}
