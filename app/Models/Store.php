<?php

namespace App\Models;

use Database\Factories\StoreFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'zone_id',
        'image_path',
        'cover_image_path',
        'rating',
        'slogan',
        'slogan_title',
        'language_code',
        'category_reference',
        'email',
        'password',
        'country_code',
        'mobile',
        'full_address',
        'pincode',
        'landmark',
        'short_description',
        'content_description',
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
        'registration_status',
        'charge_type',
        'unit_kilometers',
        'unit_price',
        'additional_price',
        'bank_name',
        'ifsc_code',
        'receipt_name',
        'account_number',
        'paypal_id',
        'upi_id',
        'cancel_policy',
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
            'rating' => 'decimal:2',
            'store_charge' => 'decimal:2',
            'unit_price' => 'decimal:2',
            'additional_price' => 'decimal:2',
        ];
    }

    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class);
    }

    public function riders(): HasMany
    {
        return $this->hasMany(Rider::class);
    }

    public function storeCategories(): HasMany
    {
        return $this->hasMany(StoreCategory::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(StoreGalleryImage::class);
    }

    public function deliveryOptions(): HasMany
    {
        return $this->hasMany(DeliveryOption::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function subscriptionOrders(): HasMany
    {
        return $this->hasMany(SubscriptionOrder::class);
    }

    public function payoutRequests(): HasMany
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function cashCollections(): HasMany
    {
        return $this->hasMany(CashCollection::class);
    }

    public function storeNotifications(): HasMany
    {
        return $this->hasMany(StoreNotification::class);
    }
}
