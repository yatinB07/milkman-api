<?php

namespace App\Repositories;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Foundation\Auth\User as Authenticatable;

class IdentityRepository
{
    /** @return class-string<Authenticatable> */
    public function modelClass(string $identityType): string
    {
        return match ($identityType) {
            'admin' => Admin::class,
            'customer' => Customer::class,
            'store' => Store::class,
            'rider' => Rider::class,
        };
    }

    public function findActiveCandidateByEmail(string $identityType, string $email): ?Authenticatable
    {
        $modelClass = $this->modelClass($identityType);

        return $modelClass::query()
            ->where('email', $email)
            ->first();
    }

    public function typeFor(Authenticatable $identity): ?string
    {
        return match (true) {
            $identity instanceof Admin => 'admin',
            $identity instanceof Customer => 'customer',
            $identity instanceof Store => 'store',
            $identity instanceof Rider => 'rider',
            default => null,
        };
    }
}
