<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'store_id',
        'product_id',
        'subscribe_price',
        'normal_price',
        'title',
        'discount',
        'is_out_of_stock',
        'is_subscription_required',
    ];

    protected function casts(): array
    {
        return [
            'discount' => 'decimal:2',
            'is_out_of_stock' => 'boolean',
            'is_subscription_required' => 'boolean',
            'normal_price' => 'decimal:2',
            'subscribe_price' => 'decimal:2',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
