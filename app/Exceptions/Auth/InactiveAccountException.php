<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class InactiveAccountException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct(__('auth.inactive_account'));
    }
}
