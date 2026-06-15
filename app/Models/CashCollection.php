<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashCollection extends Model
{
    protected $fillable = [
        'store_id',
        'amount',
        'message',
        'collected_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'collected_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
