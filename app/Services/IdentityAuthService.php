<?php

namespace App\Services;

use App\Exceptions\Auth\InactiveAccountException;
use App\Exceptions\Auth\InvalidCredentialsException;
use App\Exceptions\Auth\TokenIdentityMismatchException;
use App\Repositories\IdentityRepository;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;

class IdentityAuthService
{
    public function __construct(
        private readonly IdentityRepository $identities,
    ) {}

    public function authenticate(string $identityType, string $email, string $password): Authenticatable
    {
        $identity = $this->identities->findActiveCandidateByEmail($identityType, $email);

        if (! $identity || ! Hash::check($password, $identity->password)) {
            throw new InvalidCredentialsException;
        }

        if (! $identity->is_active) {
            throw new InactiveAccountException;
        }

        return $identity;
    }

    public function assertTokenMatchesIdentityType(Authenticatable $identity, string $identityType): void
    {
        if ($this->identities->typeFor($identity) !== $identityType) {
            throw new TokenIdentityMismatchException;
        }
    }
}
