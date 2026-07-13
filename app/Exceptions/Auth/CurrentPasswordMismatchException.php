<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CurrentPasswordMismatchException extends UnprocessableEntityHttpException
{
    public function __construct()
    {
        parent::__construct(__('auth.current_password_mismatch'));
    }
}
