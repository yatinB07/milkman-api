<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'quantity',
        'product_title',
        'discount',
        'image_path',
        'price',
        'variant_title',
    ];

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'price' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
