<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\StoreData;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'zone_id' => ['sometimes', 'nullable', 'integer', 'exists:zones,id'],
            'image_path' => ['sometimes', 'nullable', 'string'],
            'cover_image_path' => ['sometimes', 'nullable', 'string'],
            'rating' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'slogan' => ['sometimes', 'nullable', 'string', 'max:255'],
            'slogan_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'language_code' => ['sometimes', 'nullable', 'string', 'max:12'],
            'category_reference' => ['sometimes', 'nullable', 'string'],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('stores', 'email')->ignore($this->route('store')),
            ],
            'password' => ['sometimes', 'required', 'string', 'min:8'],
            'country_code' => ['sometimes', 'nullable', 'string', 'max:8'],
            'mobile' => ['sometimes', 'nullable', 'string', 'max:32'],
            'full_address' => ['sometimes', 'nullable', 'string'],
            'pincode' => ['sometimes', 'nullable', 'string', 'max:32'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'content_description' => ['sometimes', 'nullable', 'string'],
            'latitude' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
            'store_charge' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'delivery_charge' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'minimum_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'commission_percent' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'opens_at' => ['sometimes', 'nullable', 'date_format:H:i:s'],
            'closes_at' => ['sometimes', 'nullable', 'date_format:H:i:s'],
            'is_pickup_enabled' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'registration_status' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'charge_type' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'unit_kilometers' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'unit_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'additional_price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'ifsc_code' => ['sometimes', 'nullable', 'string', 'max:64'],
            'receipt_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'account_number' => ['sometimes', 'nullable', 'string', 'max:64'],
            'paypal_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'upi_id' => ['sometimes', 'nullable', 'string', 'max:255'],
            'cancel_policy' => ['sometimes', 'nullable', 'string'],
        ];
    }

    public function toData(): StoreData
    {
        return StoreData::fromArray($this->validated());
    }
}
