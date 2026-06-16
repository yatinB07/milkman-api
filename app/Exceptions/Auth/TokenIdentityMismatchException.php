<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class TokenIdentityMismatchException extends AccessDeniedHttpException
{
    public function __construct()
    {
        parent::__construct(__('auth.token_identity_mismatch'));
    }
}
