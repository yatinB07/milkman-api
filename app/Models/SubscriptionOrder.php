<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'customer_id',
        'ordered_at',
        'payment_method_id',
        'address',
        'landmark',
        'delivery_charge',
        'coupon_id',
        'coupon_amount',
        'total',
        'subtotal',
        'transaction_id',
        'admin_note',
        'admin_status',
        'rider_id',
        'wallet_amount',
        'customer_name',
        'customer_mobile',
        'status',
        'rejection_comment',
        'time_slot',
        'order_type',
        'is_rated',
        'reviewed_at',
        'total_rating',
        'rating_text',
        'commission_percent',
        'store_charge',
        'internal_status',
        'signature_path',
    ];

    protected function casts(): array
    {
        return [
            'admin_status' => 'integer',
            'commission_percent' => 'decimal:2',
            'coupon_amount' => 'decimal:2',
            'delivery_charge' => 'decimal:2',
            'is_rated' => 'boolean',
            'ordered_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'store_charge' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
            'wallet_amount' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function rider(): BelongsTo
    {
        return $this->belongsTo(Rider::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(SubscriptionOrderItem::class);
    }
}
