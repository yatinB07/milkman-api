<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SettingResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->getKey(),
            'web_name' => $this->resource->getAttribute('web_name'),
            'web_logo_path' => $this->resource->getAttribute('web_logo_path'),
            'timezone' => $this->resource->getAttribute('timezone'),
            'currency' => $this->resource->getAttribute('currency'),
            'primary_store_id' => $this->resource->getAttribute('primary_store_id'),
            'customer_onesignal_key' => $this->resource->getAttribute('customer_onesignal_key'),
            'customer_onesignal_hash' => $this->resource->getAttribute('customer_onesignal_hash'),
            'delivery_onesignal_key' => $this->resource->getAttribute('delivery_onesignal_key'),
            'delivery_onesignal_hash' => $this->resource->getAttribute('delivery_onesignal_hash'),
            'store_onesignal_key' => $this->resource->getAttribute('store_onesignal_key'),
            'store_onesignal_hash' => $this->resource->getAttribute('store_onesignal_hash'),
            'signup_credit' => $this->resource->getAttribute('signup_credit'),
            'referral_credit' => $this->resource->getAttribute('referral_credit'),
            'show_dark_mode' => $this->resource->getAttribute('show_dark_mode'),
            'google_maps_key' => $this->resource->getAttribute('google_maps_key'),
            'sms_type' => $this->resource->getAttribute('sms_type'),
            'message_auth_key' => $this->resource->getAttribute('message_auth_key'),
            'otp_template_id' => $this->resource->getAttribute('otp_template_id'),
            'twilio_account_sid' => $this->resource->getAttribute('twilio_account_sid'),
            'twilio_auth_token' => $this->resource->getAttribute('twilio_auth_token'),
            'twilio_number' => $this->resource->getAttribute('twilio_number'),
            'otp_auth_token' => $this->resource->getAttribute('otp_auth_token'),
            'primary_store' => $this->whenLoaded('primaryStore', fn (): ?array => $this->resource->getRelation('primaryStore') ? [
                'id' => $this->resource->getRelation('primaryStore')->getKey(),
                'title' => $this->resource->getRelation('primaryStore')->getAttribute('title'),
                'email' => $this->resource->getRelation('primaryStore')->getAttribute('email'),
                'mobile' => $this->resource->getRelation('primaryStore')->getAttribute('mobile'),
            ] : null),
            'created_at' => $this->resource->getAttribute('created_at')?->toISOString(),
            'updated_at' => $this->resource->getAttribute('updated_at')?->toISOString(),
        ];
    }
}
