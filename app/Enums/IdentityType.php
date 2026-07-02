<?php

namespace App\Enums;

use App\Models\Admin;
use App\Models\Customer;
use App\Models\Rider;
use App\Models\Store;
use Illuminate\Foundation\Auth\User as Authenticatable;

enum IdentityType: string
{
    case Admin = 'admin';
    case Customer = 'customer';
    case Store = 'store';
    case Rider = 'rider';

    /** @return class-string<Authenticatable> */
    public function modelClass(): string
    {
        return match ($this) {
            self::Admin => Admin::class,
            self::Customer => Customer::class,
            self::Store => Store::class,
            self::Rider => Rider::class,
        };
    }
}
