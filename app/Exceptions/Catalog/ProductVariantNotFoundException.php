<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductVariantNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('catalog.product_variant_not_found'));
    }
}
