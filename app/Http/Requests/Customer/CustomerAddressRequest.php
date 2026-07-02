<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerAddressData;
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
            'address' => ['required', 'string'],
            'landmark' => ['nullable', 'string'],
            'rider_instruction' => ['nullable', 'string'],
            'type' => ['required', 'string', 'max:50'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ];
    }

    public function toData(): CustomerAddressData
    {
        return CustomerAddressData::fromArray($this->validated());
    }
}
