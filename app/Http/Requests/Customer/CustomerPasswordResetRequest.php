<?php

namespace App\Http\Requests\Customer;

use App\Data\Customer\CustomerPasswordResetData;
use Illuminate\Foundation\Http\FormRequest;

class CustomerPasswordResetRequest extends FormRequest
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
            'mobile' => ['required', 'string', 'max:32'],
            'password' => ['required', 'string', 'min:8'],
        ];
    }

    public function toData(): CustomerPasswordResetData
    {
        return CustomerPasswordResetData::fromArray($this->validated());
    }
}
