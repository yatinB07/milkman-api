<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutRequest extends Model
{
    protected $fillable = [
        'store_id',
        'amount',
        'status',
        'proof_path',
        'requested_at',
        'request_type',
        'account_number',
        'bank_name',
        'account_name',
        'ifsc_code',
        'upi_id',
        'paypal_id',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
        ];
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class);
    }
}
