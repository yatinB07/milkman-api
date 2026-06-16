<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class InvalidCredentialsException extends UnauthorizedHttpException
{
    public function __construct()
    {
        parent::__construct('', __('auth.invalid_credentials'));
    }
}
