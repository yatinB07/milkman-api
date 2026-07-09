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
            'zone_id' => ['required', 'integer', 'exists:zones,id'],
            'image_path' => ['required', 'string'],
            'cover_image_path' => ['required', 'string'],
            'rating' => ['required', 'numeric', 'min:0'],
            'slogan' => ['required', 'string', 'max:255'],
            'slogan_title' => ['required', 'string', 'max:255'],
            'language_code' => ['nullable', 'string', 'max:12'],
            'category_reference' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255', 'unique:stores,email'],
            'password' => ['required', 'string', 'min:8'],
            'country_code' => ['nullable', 'string', 'max:8'],
            'mobile' => ['required', 'string', 'max:32'],
            'full_address' => ['required', 'string'],
            'pincode' => ['required', 'string', 'max:32'],
            'landmark' => ['required', 'string', 'max:255'],
            'short_description' => ['required', 'string'],
            'content_description' => ['required', 'string'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'store_charge' => ['required', 'numeric', 'min:0'],
            'delivery_charge' => ['required_if:charge_type,1', 'nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['required', 'numeric', 'min:0'],
            'commission_percent' => ['required', 'numeric', 'min:0'],
            'opens_at' => ['required', 'date_format:H:i:s'],
            'closes_at' => ['required', 'date_format:H:i:s'],
            'is_pickup_enabled' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'registration_status' => ['nullable', 'integer', 'min:0'],
            'charge_type' => ['required', 'integer', 'in:1,2'],
            'unit_kilometers' => ['required_if:charge_type,2', 'nullable', 'integer', 'min:0'],
            'unit_price' => ['required_if:charge_type,2', 'nullable', 'numeric', 'min:0'],
            'additional_price' => ['required_if:charge_type,2', 'nullable', 'numeric', 'min:0'],
            'bank_name' => ['required', 'string', 'max:255'],
            'ifsc_code' => ['required', 'string', 'max:64'],
            'receipt_name' => ['required', 'string', 'max:255'],
            'account_number' => ['required', 'string', 'max:64'],
            'paypal_id' => ['required', 'string', 'max:255'],
            'upi_id' => ['required', 'string', 'max:255'],
            'cancel_policy' => ['required', 'string'],
        ];
    }

    public function toData(): StoreData
    {
        return StoreData::fromArray($this->validated());
    }
}
