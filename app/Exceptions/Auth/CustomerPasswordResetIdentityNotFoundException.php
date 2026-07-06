<?php

namespace App\Exceptions\Auth;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerPasswordResetIdentityNotFoundException extends NotFoundHttpException
{
    public function __construct()
    {
        parent::__construct(__('auth.customer_password_reset_identity_not_found'));
    }
}
