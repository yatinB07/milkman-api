<?php

namespace App\Exceptions\Catalog;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidSignatureImageException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, __('catalog.invalid_signature_image'));
    }
}
