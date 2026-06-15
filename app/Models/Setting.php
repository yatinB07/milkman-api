<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    protected $fillable = [
        'web_name',
        'web_logo_path',
        'timezone',
        'currency',
        'primary_store_id',
        'customer_onesignal_key',
        'customer_onesignal_hash',
        'delivery_onesignal_key',
        'delivery_onesignal_hash',
        'store_onesignal_key',
        'store_onesignal_hash',
        'signup_credit',
        'referral_credit',
        'show_dark_mode',
        'google_maps_key',
        'sms_type',
        'message_auth_key',
        'otp_template_id',
        'twilio_account_sid',
        'twilio_auth_token',
        'twilio_number',
        'otp_auth_token',
    ];

    protected function casts(): array
    {
        return [
            'referral_credit' => 'decimal:2',
            'show_dark_mode' => 'boolean',
            'signup_credit' => 'decimal:2',
        ];
    }

    public function primaryStore(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'primary_store_id');
    }
}
