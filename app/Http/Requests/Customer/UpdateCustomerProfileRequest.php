<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerProfileData;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomerProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function toData(): CustomerProfileData
    {
        return CustomerProfileData::fromArray($this->validated());
    }
}
