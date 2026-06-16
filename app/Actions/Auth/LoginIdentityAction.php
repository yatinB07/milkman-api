<?php

namespace App\Actions\Auth;

use App\Services\IdentityAuthService;

class LoginIdentityAction
{
    public function __construct(
        private readonly IdentityAuthService $auth,
    ) {}

    /** @return array{identity: object, token: string} */
    public function execute(string $identityType, string $email, string $password): array
    {
        $identity = $this->auth->authenticate($identityType, $email, $password);

        return [
            'identity' => $identity,
            'token' => $identity->createToken($identityType.'-api-token')->plainTextToken,
        ];
    }
}
