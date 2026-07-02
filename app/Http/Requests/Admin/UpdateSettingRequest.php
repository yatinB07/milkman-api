<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\SettingData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'web_name' => ['sometimes', 'required', 'string', 'max:255'],
            'web_logo_path' => ['nullable', 'string', 'max:2048'],
            'timezone' => ['sometimes', 'required', 'timezone'],
            'currency' => ['sometimes', 'required', 'string', 'max:16'],
            'primary_store_id' => ['nullable', 'integer', 'exists:stores,id'],
            'customer_onesignal_key' => ['nullable', 'string'],
            'customer_onesignal_hash' => ['nullable', 'string'],
            'delivery_onesignal_key' => ['nullable', 'string'],
            'delivery_onesignal_hash' => ['nullable', 'string'],
            'store_onesignal_key' => ['nullable', 'string'],
            'store_onesignal_hash' => ['nullable', 'string'],
            'signup_credit' => ['sometimes', 'required', 'numeric', 'min:0'],
            'referral_credit' => ['sometimes', 'required', 'numeric', 'min:0'],
            'show_dark_mode' => ['sometimes', 'required', 'boolean'],
            'google_maps_key' => ['nullable', 'string'],
            'sms_type' => ['nullable', 'string', 'max:50'],
            'message_auth_key' => ['nullable', 'string'],
            'otp_template_id' => ['nullable', 'string'],
            'twilio_account_sid' => ['nullable', 'string'],
            'twilio_auth_token' => ['nullable', 'string'],
            'twilio_number' => ['nullable', 'string', 'max:50'],
            'otp_auth_token' => ['nullable', 'string'],
        ];
    }

    public function toData(): SettingData
    {
        return SettingData::fromArray($this->validated());
    }
}
