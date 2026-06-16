<?php

namespace App\Actions\Auth;

use App\Exceptions\Auth\MissingPermissionException;
use Illuminate\Foundation\Auth\User as Authenticatable;

class CheckIdentityPermissionAction
{
    /** @return array{permission: string, allowed: bool} */
    public function execute(Authenticatable $identity, string $permission): array
    {
        if (! $identity->can($permission)) {
            throw new MissingPermissionException;
        }

        return [
            'permission' => $permission,
            'allowed' => true,
        ];
    }
}
