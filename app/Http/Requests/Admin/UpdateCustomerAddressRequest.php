<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerAddressData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['sometimes', 'required', 'integer', 'exists:customers,id'],
            'address' => ['sometimes', 'required', 'string'],
            'landmark' => ['sometimes', 'nullable', 'string', 'max:255'],
            'rider_instruction' => ['sometimes', 'nullable', 'string'],
            'type' => ['sometimes', 'required', 'string', 'max:64'],
            'latitude' => ['sometimes', 'required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'required', 'numeric', 'between:-180,180'],
        ];
    }

    public function toData(): CustomerAddressData
    {
        return CustomerAddressData::fromArray($this->validated());
    }
}
