<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionOrderItem extends Model
{
    protected $fillable = [
        'subscription_order_id',
        'quantity',
        'product_title',
        'discount',
        'image_path',
        'price',
        'variant_title',
        'starts_at',
        'total_deliveries',
        'total_dates',
        'completed_dates',
        'selected_days',
        'time_slot',
    ];

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'price' => 'decimal:2',
            'starts_at' => 'date',
        ];
    }

    public function subscriptionOrder(): BelongsTo
    {
        return $this->belongsTo(SubscriptionOrder::class);
    }
}
