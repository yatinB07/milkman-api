<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class MissingPermissionException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct(__('auth.missing_permission'));
    }
}
