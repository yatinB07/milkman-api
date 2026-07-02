<?php

namespace App\Http\Requests\Admin;

use App\Data\Admin\CustomerAddressData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'address' => ['required', 'string'],
            'landmark' => ['nullable', 'string', 'max:255'],
            'rider_instruction' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:64'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    public function toData(): CustomerAddressData
    {
        return CustomerAddressData::fromArray($this->validated());
    }
}
