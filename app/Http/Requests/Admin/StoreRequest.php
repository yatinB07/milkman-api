<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreData;
use Illuminate\Foundation\Http\FormRequest;

class StoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'zone_id' => ['nullable', 'integer', 'exists:zones,id'],
            'image_path' => ['nullable', 'string'],
            'cover_image_path' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric', 'min:0'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'slogan_title' => ['nullable', 'string', 'max:255'],
            'language_code' => ['nullable', 'string', 'max:12'],
            'category_reference' => ['nullable', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:stores,email'],
            'password' => ['required', 'string', 'min:8'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['nullable', 'string', 'max:32'],
            'full_address' => ['nullable', 'string'],
            'pincode' => ['nullable', 'string', 'max:32'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string'],
            'content_description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'store_charge' => ['nullable', 'numeric', 'min:0'],
            'delivery_charge' => ['nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['nullable', 'numeric', 'min:0'],
            'commission_percent' => ['nullable', 'numeric', 'min:0'],
            'opens_at' => ['nullable', 'date_format:H:i:s'],
            'closes_at' => ['nullable', 'date_format:H:i:s'],
            'is_pickup_enabled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'registration_status' => ['nullable', 'integer', 'min:0'],
            'charge_type' => ['nullable', 'integer', 'min:0'],
            'unit_kilometers' => ['nullable', 'integer', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'additional_price' => ['nullable', 'numeric', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'ifsc_code' => ['nullable', 'string', 'max:64'],
            'receipt_name' => ['nullable', 'string', 'max:255'],
            'account_number' => ['nullable', 'string', 'max:64'],
            'paypal_id' => ['nullable', 'string', 'max:255'],
            'upi_id' => ['nullable', 'string', 'max:255'],
            'cancel_policy' => ['nullable', 'string'],
        ];
    }

    public function toData(): StoreData
    {
        return StoreData::fromArray($this->validated());
    }
}
