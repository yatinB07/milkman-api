<?php

namespace App\Models;

use Database\Factories\RiderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Rider extends Authenticatable
{
    /** @use HasFactory<RiderFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected string $guard_name = 'sanctum';

    protected $fillable = [
        'store_id',
        'image_path',
        'name',
        'email',
        'country_code',
        'mobile',
        'password',
        'is_active',
        'joined_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'joined_at' => 'date',
            'password' => 'hashed',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptionOrders(): HasMany
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    public function riderNotifications(): HasMany
    {
        return $this->hasMany(RiderNotification::class);
    }
}
