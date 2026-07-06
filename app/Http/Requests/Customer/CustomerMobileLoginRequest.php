<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerMobileLoginData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerMobileLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'country_code' => ['required', 'string', 'max:8'],
            'mobile' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    public function toData(): CustomerMobileLoginData
    {
        return CustomerMobileLoginData::fromArray($this->validated());
    }
}
