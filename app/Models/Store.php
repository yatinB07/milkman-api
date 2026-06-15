<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Store extends Authenticatable
{
    /** @use HasFactory<StoreFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'title',
        'email',
        'password',
        'country_code',
        'mobile',
        'full_address',
        'pincode',
        'landmark',
        'latitude',
        'longitude',
        'store_charge',
        'delivery_charge',
        'minimum_order_amount',
        'commission_percent',
        'opens_at',
        'closes_at',
        'is_pickup_enabled',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'commission_percent' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'is_active' => 'boolean',
            'is_pickup_enabled' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'minimum_order_amount' => 'decimal:2',
            'opens_at' => 'datetime:H:i:s',
            'closes_at' => 'datetime:H:i:s',
            'password' => 'hashed',
            'store_charge' => 'decimal:2',
        ];
    }

    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class);
    }
}
